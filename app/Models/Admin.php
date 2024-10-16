<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Authenticatable implements MustVerifyEmail
{

    use HasRoles, HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'username',
        'phone',
        'address',
        'img',
        'active'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z'
    ];

}
