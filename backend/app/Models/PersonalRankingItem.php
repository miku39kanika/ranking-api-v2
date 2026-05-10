<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalRankingItem extends Model
{
    protected $fillable = [
        'id',
        'personal_ranking_id',
        'rank',
        'word',
    ];

    public function ranking()
{
    return $this->belongsTo(PersonalRanking::class);
}
}
