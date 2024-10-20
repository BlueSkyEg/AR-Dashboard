<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{

    /**
     * Create a new image record.
     *
     * @param array $attributes
     * @return Image
     */
    public function createImage(array $attributes): Image
    {
        return Image::create($attributes);
    }

    /**
     * Update an existing image record.
     *
     * @param Image $image
     * @param array $attributes
     * @return Image
     */
    public function updateImage(Image $image, array $attributes): Image
    {
        $image->update($attributes);
        return $image;
    }

    /**
    * Save Image By Url
    * This method get image by url and save it in File Storage.
    *
    * @param string $url
    * @return string
    */
    public function saveImageByUrl(string $url): string
    {
        $imagePath = date('Y/') . date('m/') . Str::afterLast($url, '/');
        $imageContents = file_get_contents($url);
        Storage::put("images/$imagePath", $imageContents);

        return $imagePath;
    }

    public function createOrUpdateImage(array $data, Model $existingModel, Model $post)
    {
        if (isset($data['featured_image'])) {
            $existingImage = $existingModel->featuredImage;
            if ($existingImage) {
                if ($data['featured_image']['src'] !== $existingImage->src) {
                    $featuredImagePath = $this->saveImageByUrl($data['featured_image']['src']);
                    $existingImage->src = $featuredImagePath;
                    Storage::delete("images/{$existingImage->src}");
                }
                if (isset($data['featured_image']['alt_text'])) {
                    $existingImage->alt_text = $data['featured_image']['alt_text'];
                }
                $existingImage->save();
            } else {
                $featuredImagePath = $this->saveImageByUrl($data['featured_image']['src']);
                $featuredImageData = [
                    'src' => $featuredImagePath,
                    'alt_text' => $data['featured_image']['alt_text']
                ];
                $newImage = $this->createImage($featuredImageData);
                $post->images()->attach($newImage->id);
                $existingModel->featured_image_id = $newImage->id;
                $post->save();
            }
        }
    }

}
