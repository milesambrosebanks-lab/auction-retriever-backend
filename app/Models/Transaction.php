<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'customer_id', 'stripe_id');
    }
    // public function order()
    // {
    //     return $this->belongsTo(Order::class);
    // }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
