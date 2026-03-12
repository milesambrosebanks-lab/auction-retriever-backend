<?php

namespace App\Models;

use App\Models\User;
use Laravel\Cashier\SubscriptionItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Subscription as CashierSubscription;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends CashierSubscription
{
    protected $table = "subscriptions";
    protected $fillable = [
        'user_id',
        'type',
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'quantity',
        'trial_ends_at',
        'ends_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // public function items(): HasMany
    // {
    //     return $this->hasMany(SubscriptionItem::class);
    // }
}
