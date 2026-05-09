<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RankingItem;
use App\Models\User;
use App\Models\Comment;

class Ranking extends Model
{protected $fillable = [
    'title',
    'reading',
    'tag',
    'image_name',
    'is_item_add_limited',
    'daily_vote_limit',
    'total_vote_limit',
    'vote_permission',
    'user_id',
];

    public function items()
{
    return $this->hasMany(RankingItem::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
public function comments()
{
    return $this->hasMany(Comment::class);
}
public function tags()
{
    return $this->belongsToMany(Tag::class);
}
}
