<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'rarity',
        'image_name',
    ];

    /**
     * このアイテムを持っているユーザー一覧
     */
    public function userItems()
    {
        return $this->hasMany(UserItem::class);
    }
}