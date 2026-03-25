<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    protected $fillable = [
        'auction_id',
        'type',
        'current_bid',
        'source_url',
        'city',
        'state',
        'zip',
        'county',
        'scraped_at',
    ];

    protected $casts = [
        'current_bid' => 'decimal:2',
        'scraped_at' => 'datetime',
    ];
}
