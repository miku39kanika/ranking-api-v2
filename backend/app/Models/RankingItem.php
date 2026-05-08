<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankingItem extends Model
{
    protected $fillable = [
        'ranking_id',
        'name',
        'votes',
        'aliases',
    ];

    protected $casts = [
        'aliases' => 'array',
    ];
}