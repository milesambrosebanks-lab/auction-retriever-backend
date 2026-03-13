<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionListing extends Model
{
     protected $fillable = [
        'auction_id',
        'title',
        'type',
        'current_bid',
        'bid_count',
        'time_left',
        'image_url',
        'source_url',
        'channel_code',
        'category_code',
        'scraped_at',
    ];

    protected $casts = [
        'scraped_at' => 'datetime',
        'bid_count'  => 'integer',
    ];
}
