<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * 所持ユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * アイテム本体
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * 期限切れチェック
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->greaterThan($this->expires_at);
    }
}
