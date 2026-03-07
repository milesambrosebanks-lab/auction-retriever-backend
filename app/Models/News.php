<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\User;

class News extends Model
{
    protected $guarded = [];

    protected $appends = [
        'date',
        'short_description'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDateAttribute()
    {
        return Carbon::parse($this->created_at)->format('M j, Y'); // e.g. "Nov 8, 2025"
    }

    public function getShortDescriptionAttribute()
    {
        $shortDescription = strip_tags($this->content);

        return Str::length($shortDescription) > 200 ? Str::substr($shortDescription, 0, 200) . '...' : $shortDescription;
    }

}
