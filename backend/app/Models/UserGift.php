<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGift extends Model
{
    protected $fillable = [
        'user_id',
        'gift_id',
        'received_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }
}