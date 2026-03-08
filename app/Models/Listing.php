<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = [
        'title',
        'county',
        'state',
        'property_type',
        'starting_bid',
        'auction_date',
        'source_website',
        'source_url'
    ];

    protected $casts = [
        'auction_date' => 'date'
    ];

    public function savedListings()
    {
        return $this->hasMany(SavedListing::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'saved_listings');
    }
}