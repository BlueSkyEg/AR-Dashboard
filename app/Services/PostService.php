<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\PostTypeEnum;
use App\Models\PostCategory;
use App\Models\Post;

class PostService
{
        /**
     * @param array $attributes
     * @return Post
     */
    public function createPost(array $attributes): Post
    {
        return Post::create($attributes);
    }

    /**
     * Update an existing post record.
     *
     * @param Post $post
     * @param array $attributes
     * @return Post
     */
    public function updatePost(Post $post, array $attributes): Post
    {
        $post->update($attributes);
        return $post;
    }
}
