<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $table = "plans";
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
}
