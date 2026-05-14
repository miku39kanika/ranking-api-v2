<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'tag_image_name',
    
    ];

    public function rankings()
    {
        return $this->belongsToMany(Ranking::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
