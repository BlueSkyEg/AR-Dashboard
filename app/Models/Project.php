<?php

namespace App\Models;

use App\Models\DonationForm;
use App\Models\Image;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'slug',
        'post_id',
        'featured_image_id',
        'donation_form_id',
        'order'
    ];

    // Project belongs to one post
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // Project belongs to one featured image
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'featured_image_id');
    }

    // Project belongs to one donation form
    public function donationForm(): BelongsTo
    {
        return $this->belongsTo(DonationForm::class);
    }
}
