<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'fully_fund_level',
        'levels'
    ];

    protected $casts = [
        'levels' => 'array',
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z'
    ];
}
