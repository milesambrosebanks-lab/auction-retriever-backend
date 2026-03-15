<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;

// class AuctionListing extends Model
// {
//      protected $fillable = [
//         'auction_id',
//         'title',
//         'type',
//         'current_bid',
//         'bid_count',
//         'time_left',
//         'image_url',
//         'source_url',
//         'channel_code',
//         'category_code',
//         'scraped_at',
//     ];

//     protected $casts = [
//         'scraped_at' => 'datetime',
//         'bid_count'  => 'integer',
//     ];
// }
// app/Models/AuctionListing.php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionListing extends Model
{
    protected $fillable = [
        'auction_id',
        'title',
        'type',
        'state',
        'county',
        'current_bid',
        'bid_amount',
        'bid_count',
        'time_left',
        'auction_date',
        'image_url',
        'source_url',
        'channel_code',
        'category_code',
        'scraped_at',
        'city',
        'zip',
        'auction_started_at',
        'parcel_number', // ← add করো
    ];

    protected $casts = [
        'scraped_at'   => 'datetime',
        'auction_date' => 'datetime',
        'bid_count'    => 'integer',
        'bid_amount'   => 'decimal:2',
    ];

    public function savedByUsers()
    {
        return $this->hasMany(SavedListing::class);
    }

    public function isSavedBy(int $userId): bool
    {
        return $this->savedByUsers()->where('user_id', $userId)->exists();
    }
}
