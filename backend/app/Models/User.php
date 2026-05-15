<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'public_id',
        'device_id',
        'email',
        'plan_type',
        'is_deleted',
        'banned_at',
        'icon_type',
        'icon_name',
        'user_name',
        'about_self',
        'point_stone'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_deleted' => 'boolean',
        'banned_at' => 'datetime',
    ];

    public function followings()
{
    return $this->belongsToMany(
        User::class,
        'follows',
        'follower_id',
        'followed_id'
    );
}

public function followers()
{
    return $this->belongsToMany(
        User::class,
        'follows',
        'followed_id',
        'follower_id'
    );
}
public function tags()
{
    return $this->belongsToMany(Tag::class);
}
}
