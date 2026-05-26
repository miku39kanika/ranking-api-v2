<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonCrownRanking extends Model
{
    protected $fillable = [
        'season',
        'user_id',
        'crown_amount',
        'rank',
        'snapshot_date',
    ];
}
