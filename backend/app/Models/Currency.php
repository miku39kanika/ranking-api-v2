<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'id',
        'code',
        'name',
        'icon',
        'description',
        'is_active'
    ];
}
