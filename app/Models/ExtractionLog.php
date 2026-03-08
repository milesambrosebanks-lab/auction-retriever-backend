<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtractionLog extends Model
{
    protected $fillable = [
        'source',
        'last_successful_run',
        'status',
        'message'
    ];

    protected $casts = [
        'last_successful_run' => 'datetime',
        'status' => 'boolean'
    ];
}