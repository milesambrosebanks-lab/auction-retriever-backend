<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingSetting extends Model
{
    protected $fillable = [
        'meta_pixel_id',
        'ga_measurement_id',
        'clarity_id',
        'meta_enabled',
        'ga_enabled',
        'clarity_enabled',
    ];

    protected $casts = [
        'meta_enabled'    => 'boolean',
        'ga_enabled'      => 'boolean',
        'clarity_enabled' => 'boolean',
    ];

    // সবসময় single row — singleton pattern
    public static function instance(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
