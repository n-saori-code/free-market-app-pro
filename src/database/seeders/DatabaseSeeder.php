<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->copyDefaultImages();

        $this->call([
            UsersTableSeeder::class,
            CategoriesTableSeeder::class,
            ConditionsTableSeeder::class,
            ProductsTableSeeder::class,
        ]);
    }

    private function copyDefaultImages(): void
    {
        $paths = [
            'profile_images/default.jpg',
            'products/armani_mens_clock.jpg',
            'products/hdd_hard_disk.jpg',
            'products/onion_bundle.jpg',
            'products/leather_shoes.jpg',
            'products/laptop.jpg',
            'products/mic.jpg',
            'products/shoulder_bag.jpg',
            'products/tumbler.jpg',
            'products/coffee_mill.jpg',
            'products/makeup_set.jpg',
        ];

        foreach ($paths as $path) {
            if (!Storage::disk('public')->exists($path)) {
                $source = public_path('images/' . $path);
                if (file_exists($source)) {
                    Storage::disk('public')->put($path, file_get_contents($source));
                }
            }
        }
    }
}
