<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\PostContentEnum;
use App\Http\Resources\ImageResource;

class PostContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type,
            'body' => $this->when(
                $this->type === PostContentEnum::IMAGE->value,
                new ImageResource($this->image), // If the type is image, return the image resource
                $this->body // Otherwise, return the processed body directly
            ),
            'order' => $this->order
        ];
    }
}
