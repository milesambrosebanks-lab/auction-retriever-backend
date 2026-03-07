<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Police extends Model
{
    protected $table = 'polices';
    protected $guarded = [];

    protected $appends = [
        'short_description'
    ];

    public function getShortDescriptionAttribute()
    {
        $shortDescription = strip_tags($this->content);

        return Str::length($shortDescription) > 200 ? Str::substr($shortDescription, 0, 200) . '...' : $shortDescription;
    }
    
}
