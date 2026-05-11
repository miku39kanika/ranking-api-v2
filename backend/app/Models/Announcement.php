<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'important',
        'send_at',
    ];

    protected $casts = [
        'important' => 'boolean',
        'send_at' => 'datetime',
    ];
}