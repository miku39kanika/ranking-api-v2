<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankingInvite extends Model
{
    protected $fillable = [
        'ranking_id',
        'user_id',
    ];
    public function ranking()
    {
        return $this->belongsTo(Ranking::class);
    }
}
