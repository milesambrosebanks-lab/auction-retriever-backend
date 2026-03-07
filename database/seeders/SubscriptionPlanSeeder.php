<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubscriptionPlan::create([
            'name' => 'Monthly Plan',
            'stripe_product_id'=>'pk_xxxxxxxxxx',
            'stripe_price_id' => 'price_xxxxxxxxx',
            'price' => 29,
            'trial_days' => 7,
            'interval' => 'month'
        ]);
    }
}
