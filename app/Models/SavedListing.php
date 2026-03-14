<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedListing extends Model
{
    protected $fillable = ['user_id', 'auction_listing_id'];

    public function listing()
    {
        return $this->belongsTo(AuctionListing::class, 'auction_listing_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
