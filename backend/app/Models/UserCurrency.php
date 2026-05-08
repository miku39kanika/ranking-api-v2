<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCurrency extends Model
{
    protected $fillable = [
        'user_id',
        'currency_id',
        'amount',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}