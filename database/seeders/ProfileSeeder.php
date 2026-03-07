<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('profiles')->insert([
            [
                'user_id'    => 1,
                'first_name' => "Admin",
                'last_name'  => "Admin",
                'phone'      => "123456789",
                'gender'     => "male",
                'dob'        => "2022-12-01",
                'country'    => "egypt",
                'city'       => "cairo",
                'address'    => "cairo",
                'postal_code'=> "12345",
                'message'    => "hello",
            ],
            [
                'user_id'    => 2,
                'first_name' => "member",
                'last_name'  => "member",
                'phone'      => "1234565789",
                'gender'     => "male",
                'dob'        => "2022-12-01",
                'country'    => "egypt",
                'city'       => "cairo",
                'address'    => "cairo",
                'postal_code'=> "12345",
                'message'    => "hello", 
            ]
        ]);
    }
}
