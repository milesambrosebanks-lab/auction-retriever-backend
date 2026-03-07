<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = [];

        protected $fillable = [
        'name',
        'stripe_product_id',
        'stripe_price_id',
        'price',
        'currency',
        'interval',
        'interval_count',
        'trial_days',
        'status'
    ];

    public function features()
    {
        return $this->hasMany(Feature::class);
    }   
    // public function users()
    // {
    //     return $this->hasMany(User::class);
    // }
}
