<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    protected $fillable = [
        'title',
        'body',
        'case',
        'user_id',
        'expires_at',
        'from_date',
        'send_at',
    ];

    protected $casts = [
        'send_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}