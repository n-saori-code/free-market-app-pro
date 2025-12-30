<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        $product = Product::create([
            'user_id' => 2,
            'condition_id' => 1,
            'image' => 'products/armani_mens_clock.jpg',
            'title' => '腕時計',
            'brand' => 'Rolax',
            'description' => 'スタイリッシュなデザインのメンズ腕時計',
            'price' => 15000,
        ]);
        $product->categories()->attach(
            Category::whereIn('category_name', ['ファッション', 'メンズ'])->pluck('id')
        );

        $product = Product::create([
            'user_id' => 2,
            'condition_id' => 2,
            'image' => 'products/hdd_hard_disk.jpg',
            'title' => 'HDD',
            'brand' => '西芝',
            'description' => '高速で信頼性の高いハードディスク',
            'price' => 5000,
        ]);
        $product->categories()->attach(
            Category::where('category_name', '家電')->pluck('id')
        );

        $product = Product::create([
            'user_id' => 2,
            'condition_id' => 3,
            'image' => 'products/onion_bundle.jpg',
            'title' => '玉ねぎ3束',
            'brand' => 'なし',
            'description' => '新鮮な玉ねぎ3束のセット',
            'price' => 300,
        ]);
        $product->categories()->attach(
            Category::whereIn('category_name', ['キッチン'])->pluck('id')
        );

        $product = Product::create([
            'user_id' => 2,
            'condition_id' => 4,
            'image' => 'products/leather_shoes.jpg',
            'title' => '革靴',
            'brand' => null,
            'description' => 'クラシックなデザインの革靴',
            'price' => 4000,
        ]);
        $product->categories()->attach(
            Category::whereIn('category_name', ['ファッション', 'メンズ'])->pluck('id')
        );

        $product = Product::create([
            'user_id' => 2,
            'condition_id' => 1,
            'image' => 'products/laptop.jpg',
            'title' => 'ノートPC',
            'brand' => null,
            'description' => '高性能なノートパソコン',
            'price' => 45000,
        ]);
        $product->categories()->attach(
            Category::whereIn('category_name', ['家電'])->pluck('id')
        );

        $product = Product::create([
            'user_id' => 3,
            'condition_id' => 2,
            'image' => 'products/mic.jpg',
            'title' => 'マイク',
            'brand' => 'なし',
            'description' => '高音質のレコーディング用マイク',
            'price' => 8000,
        ]);
        $product->categories()->attach(
            Category::whereIn('category_name', ['家電', 'ゲーム'])->pluck('id')
        );

        $product = Product::create([
            'user_id' => 3,
            'condition_id' => 3,
            'image' => 'products/shoulder_bag.jpg',
            'title' => 'ショルダーバッグ',
            'brand' => null,
            'description' => 'おしゃれなショルダーバッグ',
            'price' => 3500,
        ]);
        $product->categories()->attach(
            Category::whereIn('category_name', ['ファッション', 'レディース'])->pluck('id')
        );

        $product = Product::create([
            'user_id' => 3,
            'condition_id' => 4,
            'image' => 'products/tumbler.jpg',
            'title' => 'タンブラー',
            'brand' => 'なし',
            'description' => '使いやすいタンブラー',
            'price' => 500,
        ]);
        $product->categories()->attach(
            Category::whereIn('category_name', ['キッチン', 'インテリア'])->pluck('id')
        );

        $product = Product::create([
            'user_id' => 3,
            'condition_id' => 1,
            'image' => 'products/coffee_mill.jpg',
            'title' => 'コーヒーミル',
            'brand' => 'Starbacks',
            'description' => '手動のコーヒーミル',
            'price' => 4000,
        ]);
        $product->categories()->attach(
            Category::whereIn('category_name', ['キッチン', '家電'])->pluck('id')
        );

        $product = Product::create([
            'user_id' => 3,
            'condition_id' => 2,
            'image' => 'products/makeup_set.jpg',
            'title' => 'メイクセット',
            'brand' => null,
            'description' => '便利なメイクアップセット',
            'price' => 2500,
        ]);
        $product->categories()->attach(
            Category::whereIn('category_name', ['コスメ', 'レディース'])->pluck('id')
        );
    }
}
