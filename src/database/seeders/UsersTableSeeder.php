<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ja_JP');

        // 管理者ユーザー
        if (!DB::table('users')->where('email', 'admin@example.com')->exists()) {
            $adminId = DB::table('users')->insertGetId([
                'name' => '管理者ユーザー',
                'email' => 'admin@example.com',
                'password' => Hash::make('adminpassword'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $postalCodeAdmin = preg_replace('/(\d{3})(\d{4})/', '$1-$2', $faker->postcode());
            DB::table('profiles')->insert([
                'user_id' => $adminId,
                'profile_image' => 'profile_images/default.jpg',
                'postal_code' => $postalCodeAdmin,
                'address' => mb_substr($faker->prefecture() . $faker->city() . $faker->streetAddress(), 0, 255),
                'building' => $faker->optional()->secondaryAddress(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 一般ユーザー1
        if (!DB::table('users')->where('email', 'user001@example.com')->exists()) {
            $userId = DB::table('users')->insertGetId([
                'name' => '一般ユーザー001',
                'email' => 'user001@example.com',
                'password' => Hash::make('password001'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $postalCodeUser = preg_replace('/(\d{3})(\d{4})/', '$1-$2', $faker->postcode());
            DB::table('profiles')->insert([
                'user_id' => $userId,
                'profile_image' => 'profile_images/default.jpg',
                'postal_code' => $postalCodeUser,
                'address' => mb_substr($faker->prefecture() . $faker->city() . $faker->streetAddress(), 0, 255),
                'building' => $faker->optional()->secondaryAddress(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 一般ユーザー2
        if (!DB::table('users')->where('email', 'user002@example.com')->exists()) {
            $userId = DB::table('users')->insertGetId([
                'name' => '一般ユーザー002',
                'email' => 'user002@example.com',
                'password' => Hash::make('password002'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $postalCodeUser = preg_replace('/(\d{3})(\d{4})/', '$1-$2', $faker->postcode());
            DB::table('profiles')->insert([
                'user_id' => $userId,
                'profile_image' => 'profile_images/default.jpg',
                'postal_code' => $postalCodeUser,
                'address' => mb_substr($faker->prefecture() . $faker->city() . $faker->streetAddress(), 0, 255),
                'building' => $faker->optional()->secondaryAddress(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 一般ユーザー3
        if (!DB::table('users')->where('email', 'user003@example.com')->exists()) {
            $userId = DB::table('users')->insertGetId([
                'name' => '一般ユーザー003',
                'email' => 'user003@example.com',
                'password' => Hash::make('password003'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $postalCodeUser = preg_replace('/(\d{3})(\d{4})/', '$1-$2', $faker->postcode());
            DB::table('profiles')->insert([
                'user_id' => $userId,
                'profile_image' => 'profile_images/default.jpg',
                'postal_code' => $postalCodeUser,
                'address' => mb_substr($faker->prefecture() . $faker->city() . $faker->streetAddress(), 0, 255),
                'building' => $faker->optional()->secondaryAddress(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
