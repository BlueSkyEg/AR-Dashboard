<?php

namespace App\Models;

use App\Models\Image;
use App\Casts\PostContentBodyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'type',
        'body',
        'order'
    ];

    protected $casts = [
        'body' => PostContentBodyCast::class,
    ];

    public function image()
    {
        return $this->hasOne(Image::class, 'id', 'body');
    }
}
