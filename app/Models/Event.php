<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $guarded = [];

    protected $appends = [
        'short_description'
    ];

    public function getShortDescriptionAttribute()
    {
        $shortDescription = strip_tags($this->description);

        return Str::length($shortDescription) > 200 ? Str::substr($shortDescription, 0, 200) . '...' : $shortDescription;
    }

    public function eventRegisters()
    {
        return $this->hasMany(EventRegister::class);
    }

    public function speakers()
    {
        return $this->belongsToMany(Leader::class, 'event_leader', 'event_id', 'leader_id');
    }
}
