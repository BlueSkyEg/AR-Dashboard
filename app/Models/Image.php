<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'src',
        'alt_text'
    ];

     // Handle image path
    protected function src(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => asset("storage/images/$value")
        );
    }
}
