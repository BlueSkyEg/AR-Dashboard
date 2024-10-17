<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\PostTypeEnum;
use Illuminate\Support\Facades\Log;

class CrudService
{

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
     * Generic function to update a record.
     *
     * @param Model $model
     * @param int $id
     * @param array $data
     * @return Model|null
     */
    public function update(Model $model, int $id, array $data): ?Model
    {
        $record = $this->read($model, $id);

        if ($record) {
            $record->update($data);
            return $record;
        }

        return null;
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
