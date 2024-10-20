<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\PostTypeEnum;
use App\Models\PostCategory;

class PostCategoryService
{

    /**
     * Get post categories by their slug and post type.
     *
     * @param array $categoriesSlug
     * @param PostTypeEnum $postType
     * @return Collection|null
     */
    public function getPostCategoriesBySlug(array $categoriesSlug, PostTypeEnum $postType): ?Collection
    {
        // Directly query the PostCategory model for the slugs and post type.
        return PostCategory::whereIn('slug', $categoriesSlug)
                        ->where('post_type', $postType)
                        ->get();
    }

     /**
     * Create a new Post Category from given attributes.
     *
     * @param PostTypeEnum $postType
     * @param array $categoryData
     * @return PostCategory
     */
    public function createPostCategory(PostTypeEnum $postType, array $categoryData): PostCategory
    {
        try {
            // Prepare the category data with the post type
            $categoryAttributes = [
                'post_type' => $postType,
                'name' => $categoryData['name'],
                'slug' => $categoryData['slug'],
                'meta_title' => $categoryData['meta_title'] ?? null,
                'meta_keywords' => $categoryData['meta_keywords'] ?? null,
                'meta_description' => $categoryData['meta_description'] ?? null,
                'meta_robots' => $categoryData['meta_robots'] ?? null,
                'meta_og_type' => $categoryData['meta_og_type'] ?? null,
                'order' => $categoryData['order'] ?? null
            ];

            // Create and return the post category
            return PostCategory::create($categoryAttributes);
        } catch (\Exception $e) {
            // Log any errors
            Log::error('Error creating post category: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createOrUpdateCategory(array $data, Model $post, string $className)
    {
        // Check if categories key is provided
        if (isset($data['categories'])) {
            $categories = $this->getPostCategoriesBySlug(
                array_column($data['categories'], 'slug'), // Extract slugs from all provided categories
                $className == 'App\Models\Project' ? PostTypeEnum::PROJECT : PostTypeEnum::BLOG
            );

            // Sync the existing categories with the post
            $post->categories()->sync($categories?->pluck('id'));
        }

        // Check if categories_data key is provided - Can create array of categories at once
        if (isset($data['categories_data'])) {
            $newCategoryIds = []; // To store the IDs of newly created categories

            foreach ($data['categories_data'] as $categoryData) {
                // Determine the post type
                $postType = $className == 'App\Models\Project' ? PostTypeEnum::PROJECT : PostTypeEnum::BLOG;

                // Create the new category with the provided attributes
                $categoryAttributes = [
                    'post_type' => $postType,
                    'name' => $categoryData['name'],
                    'slug' => $categoryData['slug'],
                    'meta_title' => $categoryData['meta_title'] ?? null,
                    'meta_keywords' => $categoryData['meta_keywords'] ?? null,
                    'meta_description' => $categoryData['meta_description'] ?? null,
                    'meta_robots' => $categoryData['meta_robots'] ?? null,
                    'meta_og_type' => $categoryData['meta_og_type'] ?? null,
                    'order' => $categoryData['order'] ?? null
                ];

                // Create the new category and get the ID
                $category = PostCategory::create($categoryAttributes);
                $newCategoryIds[] = $category->id; // Add the new category ID to the array
            }

            // Attach newly created categories to the post
            $post->categories()->attach($newCategoryIds);
        }
    }

}
