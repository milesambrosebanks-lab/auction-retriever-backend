<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Leader extends Model
{
    protected $guarded = ['id'];

    protected $appends = [
        'short_description'
    ];

    public function getThumbnailAttribute($value): string | null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url($value);
        }

        // Return only the path for web requests
        return $value;
    }

    public function getShortDescriptionAttribute()
    {
        $shortDescription = strip_tags($this->content);

        return Str::length($shortDescription) > 200 ? Str::substr($shortDescription, 0, 200) . '...' : $shortDescription;
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_leader', 'event_id', 'leader_id');
    }

    
}
