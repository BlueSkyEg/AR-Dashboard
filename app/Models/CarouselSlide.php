<?php

namespace App\Models;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarouselSlide extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'destination_label',
        'destination_type',
        'destination_slug',
        'image_id',
        'carousel_type'
    ];

    // Slide has one image
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }
}
