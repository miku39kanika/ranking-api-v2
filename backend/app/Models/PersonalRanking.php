<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalRanking extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'title',
    ];
    public function items()
    {
        return $this->hasMany(PersonalRankingItem::class);
    }
}
