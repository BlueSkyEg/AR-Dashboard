<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\PostTypeEnum;
use Illuminate\Support\Facades\Log;
use App\Services\ImageService;
use App\Services\DonationFormService;
use App\Services\PostCategoryService;
use App\Services\PostService;
use App\Services\PostContentService;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class CrudService
{

    public function __construct(
        private readonly ImageService $imageService,
        private readonly PostCategoryService $postCategoryService,
        private readonly DonationFormService $donationFormService,
        private readonly PostService $postService,
        private readonly PostContentService $postContentService)
    {
    }

    /**
     * Generic function to get all records with filtering by category, published status, and pagination.
     *
     * @param string $modelClass       The model class (e.g., Project::class, Blog::class, Career::class).
     * @param int $perPage             Number of items per page.
     * @param string|null $categorySlug Optional category slug to filter by.
     * @param bool|null $published     Optional filter for published status.
     * @return LengthAwarePaginator
     */
    public function getAll(string $modelClass,int $perPage,string|null $categorySlug = null,bool|null $published = null): LengthAwarePaginator {

        return $modelClass::when($published === true, function (Builder $query) {
                return $query->whereRelation('post', 'published', '=', 1);
            })
            ->when($published === false, function (Builder $query) {
                return $query->whereRelation('post', 'published', '=', 0);
            })
            ->when($categorySlug, function (Builder $query) use ($categorySlug, $modelClass) {
                return $query->whereHas('post.categories', function (Builder $query) use ($categorySlug, $modelClass) {
                    $postType = $this->getPostTypeForModel($modelClass);
                    if ($postType) {
                        $query->where('post_type', '=', $postType);
                    }
                    $query->where('slug', '=', $categorySlug);
                });
            })
            ->when($modelClass === Career::class, function (Builder $query) {
                return $query->latest('id');
            }, function (Builder $query) {
                $query->whereHas('post', function (Builder $query) {
                    $query->orderBy('created_at', 'desc');
                })
                ->with('post.categories', 'featuredImage');
            })
            ->orderBy('order', 'asc')
            ->paginate($perPage);
    }

    /**
     * Helper function to return the post type for a given model.
     *
     * @param string $modelClass
     * @return string|null
     */
    private function getPostTypeForModel(string $modelClass): ?string
    {
        return match ($modelClass) {
            Project::class => PostTypeEnum::PROJECT->value,
            Blog::class => PostTypeEnum::BLOG->value,
            default => null,
        };
    }

    /**
     * Generic function to read a record by ID or slug.
     *
     * @param Model $model
     * @param int|null $id
     * @param string|null $slug
     * @param bool|null $published
     * @return Model|null
     */
    public function read(Model $model, int|null $id = null, bool|null $published = null): ?Model
    {
        if ($id !== null) {
            return $model->where('id', $id)
                ->when($published, function (Builder $query) {
                    return $query->whereRelation('post', 'published', '=', 1);
                })
                ->with([
                    'post' => ['images', 'categories', 'contents'],
                    'featuredImage',
                    'donationForm'
                ])->first();
        }
        return null;
    }

    /**
     * Generic function to create a record.
     *
     * @param array $data
     * @param Model $model
     * @return Model|null
     */
    public function create(array $data, Model $model): ?Model
    {
        try {
            // Save featured image in file storage and database
            $featuredImagePath = $this->imageService->saveImageByUrl($data['featured_image']['src']);
            $featuredImageData = [
                'src' => $featuredImagePath,
                'alt_text' => $data['featured_image']['alt_text']
            ];
            $featuredImage = $this->imageService->createImage($featuredImageData);

            // Create post for the model (Project or Blog)
            $postData = [
                'title' => $data['title'],
                'excerpt' => $data['excerpt'],
                'published' => 1,
                'meta_title' => $data['meta_title'],
                'meta_keywords' => $data['meta_keywords'],
                'meta_description' => $data['meta_description'],
                'meta_robots' => $data['meta_robots'],
                'meta_og_type' => $data['meta_og_type']
            ];
            $post = $this->postService->createPost($postData);

            // Create the model (either Project or Blog)
            $modelData = [
                'slug' => $data['slug'],
                'donation_form_id' => $data['donation_form_id'],
                'featured_image_id' => $featuredImage->id,
                'order' => $data['order'] ?? null
            ];

            $className = get_class($model);

            if ($className == 'App\Models\Project') {
                $post->project()->create($modelData);
            } elseif ($className == 'App\Models\Blog') {
                $modelData['location'] = $data['location'];
                $modelData['implementation_date'] = \Carbon\Carbon::parse($data['implementation_date'])->toDateTimeString();
                $post->blog()->create($modelData);
            }

            // Create contents for the post
            $contents = [];
            foreach ($data['contents'] as $index => $content) {
                $contentBody = $content['body'];

                if ($content['type'] === 'image') {
                    $imagePath = $this->imageService->saveImageByUrl($contentBody['src']);
                    $imageData = [
                        'src' => $imagePath,
                        'alt_text' => $contentBody['alt_text']
                    ];
                    $image = $this->imageService->createImage($imageData);

                    $contentBody = $image->id;
                }

                $contents[] = [
                    'type' => $content['type'],
                    'body' => $contentBody,
                    'order' => $index
                ];
            }
            $post->contents()->createMany($contents);

            // Attach categories
            $categories = $this->postCategoryService->getPostCategoriesBySlug([$data['categories'][0]['slug']], $className == 'App\Models\Project' ? PostTypeEnum::PROJECT : PostTypeEnum::BLOG);
            $post->categories()->attach($categories?->pluck('id'));

            // Return the created model (Project or Blog)
            return $className == 'App\Models\Project' ? $post->project : $post->blog;

        } catch (Exception $e) {
            Log::debug('Create Error Message: ' . $e->getMessage() . ', With Status Code: ' . $e->getCode());
            throw $e;  // Rethrow exception to allow handling outside of this service
        }
    }

    /**
    * Generic function to update a record.
     * @param array $data
     * @param Model $model
     * @param int $id
     * @return Model|null
     */
    public function update(array $data, Model $model, int $id): ?Model
    {
        try {
            // Retrieve the existing model (Project or Blog)
            $existingModel = $this->read($model, $id);
            if (!$existingModel) {
                throw new Exception('Model not found for the given ID.');
            }
            $post = $existingModel->post;
            if (!$post) {
                throw new Exception('Post not found for the given model.');
            }
            $postData = [
                'title' => $data['title'],
                'excerpt' => $data['excerpt'],
                'published' => $data['published'],
                'meta_title' => $data['meta_title'],
                'meta_keywords' => $data['meta_keywords'],
                'meta_description' => $data['meta_description'],
                'meta_robots' => $data['meta_robots'],
                'meta_og_type' => $data['meta_og_type']
            ];
            $updatedPost = $this->postService->updatePost($post, $postData);

            // Prepare model data for update
            $modelData = [
                'slug' => $data['slug'],
                'donation_form_id' => $data['donation_form_id'] ??  $this->donationFormService->createDonationForm($data, $existingModel),
                'order' => $data['order'] ?? null
            ];

            // Choose the class name and handle project or blog-specific updates
            $className = get_class($existingModel);
            if ($className == 'App\Models\Project') {
                $existingModel->update($modelData);
            } elseif ($className == 'App\Models\Blog') {
                $modelData['location'] = $data['location'];
                $modelData['implementation_date'] = \Carbon\Carbon::parse($data['implementation_date'])->toDateTimeString();
                $existingModel->update($modelData);
            }

            // Call specific update functions
            $this->imageService->createOrUpdateImage($data, $existingModel, $post);
            $this->postContentService->createOrUpdateContent($data, $post);
            $this->postCategoryService->createOrUpdateCategory($data, $post, $className);

            return $existingModel;
            
        } catch (Exception $e) {
            Log::debug('Update Error Message: ' . $e->getMessage() . ', With Status Code: ' . $e->getCode());
            throw $e;
        }
    }

    /**
     * Generic function to soft delete a record by updating the 'deleted_at' column in the related 'post'.
     *
     * @param Model $model
     * @param int $id
     * @return bool
     */
    public function delete(Model $model, int $id): bool
    {
        // Find the record by its ID
        $record = $model->find($id);

        if (!$record) {
            Log::error("Model not found with ID: $id");
            return false;
        }

        // Soft delete the post if it exists
        if ($record->post && !$record->post->delete()) {
            Log::error("Failed to soft delete post for post ID: $id");
            return false;
        }

        // Now delete (soft delete) the model itself
        if ($record->delete()) {
            Log::info("Successfully soft deleted model with ID: $id");
            return true;
        } else {
            Log::error("Failed to soft delete model with ID: $id");
        }

        return false;
    }

}
