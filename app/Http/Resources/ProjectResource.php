<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\DonationFormResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\PostCategoryBriefResource;
use App\Http\Resources\PostContentResource;

class ProjectResource extends JsonResource
{
 /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'title' => $this->post->title,
            'slug' => $this->slug,
            'excerpt' => $this->post->excerpt,
            'published' => $this->post->published,
            'categories' => PostCategoryBriefResource::collection($this->post->categories),
            'contents' => PostContentResource::collection($this->post->contents),
            'featured_image' => new ImageResource($this->featuredImage),
            'donation_form' => new DonationFormResource($this->donationForm),
            'meta_data' => [
                'meta_title' => $this->post->meta_title,
                'meta_keywords' => $this->post->meta_keywords,
                'meta_description' => $this->post->meta_description,
                'meta_robots' => $this->post->meta_robots,
                'meta_og_type' => $this->post->meta_og_type,
            ],
            'created_at' => $this->post->created_at,
            'updated_at' => $this->post->updated_at
        ];
    }
}
