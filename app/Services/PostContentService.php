<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\PostTypeEnum;
use App\Models\PostCategory;

class PostContentService
{


    public function createOrUpdateContent(array $data, Model $post)
    {
        $contents = [];
        foreach ($data['contents'] as $index => $content) {
            $contentBody = $content['body'];
            if ($content['type'] === 'image') {
                $imagePath = $this->imageService->saveImageByUrl($contentBody['src']);
                $imageData = [
                    'src' => $imagePath,
                    'alt_text' => $contentBody['alt_text']
                ];
                $image = $this->imageService->updateImage($imageData);
                $contentBody = $image->id;
            }
            $contents[] = [
                'type' => $content['type'],
                'body' => $contentBody,
                'order' => $index
            ];
        }
        $post->contents()->delete();
        $post->contents()->createMany($contents);
    }

    
}
