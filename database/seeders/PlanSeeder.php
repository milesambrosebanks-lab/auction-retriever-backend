<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('plans')->insert([
            [
                'name' => 'five',
                'description' => 'five',
                'price' => 5,
                'stripe_price_id' => 'price_1RLzRaKoqeKH4TXiujrhKAZe'
            ],
            [
                'name' => 'ten',
                'description' => 'ten',
                'price' => 10,
                'stripe_price_id' => 'price_1RLzSNKoqeKH4TXii4GM2eVc'
            ],
            [
                'name' => 'twenty',
                'description' => 'twenty',
                'price' => 20,
                'stripe_price_id' => 'price_1RLzT5KoqeKH4TXiVYtuzoxA'
            ],
            [
                'name' => 'fifty',
                'description' => 'fifty',
                'price' => 50,
                'stripe_price_id' => 'price_1RLzWnKoqeKH4TXiHVtFyEUu'
            ],
            [
                'name' => 'hundred',
                'description' => 'hundred',
                'price' => 100,
                'stripe_price_id' => 'price_1RLzbHKoqeKH4TXiGH09nguJ'
            ],
            [
                'name' => 'twohundred',
                'description' => 'twohundred',
                'price' => 200,
                'stripe_price_id' => 'price_1RLzbWKoqeKH4TXi11uQrBq0'
            ],
            [
                'name' => 'fivehundred',
                'description' => 'fivehundred',
                'price' => 500,
                'stripe_price_id' => 'price_1RLzbuKoqeKH4TXiwc8LvCpM'
            ]
        ]);
    }
}
