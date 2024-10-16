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

}
