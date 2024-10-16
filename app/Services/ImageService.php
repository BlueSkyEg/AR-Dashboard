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
        return Image::updateOrCreate($attributes);
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
}
