<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('ja_JP');

        $userId = DB::table('users')->insertGetId([
            'name' => mb_substr('テストユーザー', 0, 20),
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $postalCode = preg_replace('/(\d{3})(\d{4})/', '$1-$2', $faker->postcode());

        DB::table('profiles')->insert([
            'user_id' => $userId,
            'profile_image' => 'profile_images/default.jpg',
            'postal_code' => $postalCode,
            'address' => mb_substr($faker->prefecture() . $faker->city() . $faker->streetAddress(), 0, 255),
            'building' => $faker->optional()->secondaryAddress(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
