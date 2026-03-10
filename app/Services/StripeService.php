<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Price;
use Stripe\Product;
use Stripe\Subscription;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCustomer($user)
    {
        return Customer::create([
            'email' => $user->email,
            'name' => $user->name,
            //remove meta if any error
            'metadata' => [
                'app' => config('app.name')
            ]
        ]);
    }

    public function createSubscription($customerId, $priceId, $paymentMethod, $trialDays)
    {
        return Subscription::create([
            'customer' => $customerId,
            'items' => [
                ['price' => $priceId]
            ],
            'default_payment_method' => $paymentMethod,
            'trial_period_days' => $trialDays
        ]);
    }

    public function archivePrice($priceId)
    {

        return Price::update($priceId, [
            'active' => false
        ]);
    }

    public function archiveProduct($productId)
    {

        return Product::update($productId, [
            'active' => false
        ]);
    }
}
