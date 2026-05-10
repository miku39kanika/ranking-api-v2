<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
   protected $fillable = [
    'ranking_item_id',
    'ranking_id',
    'user_identifier',
    'vote_date',
];

public function rankingItem()
{
    return $this->belongsTo(RankingItem::class);
}
public function ranking()
{
    return $this->belongsTo(Ranking::class);
}
}
