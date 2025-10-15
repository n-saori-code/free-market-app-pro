<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Condition;

class ProductFactory extends Factory
{
    protected $model = \App\Models\Product::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'condition_id' => Condition::factory(),
            'image' => 'products/' . $this->faker->image('public/storage/products', 640, 480, null, false),
            'title' => $this->faker->word(),
            'brand' => $this->faker->company(),
            'description' => $this->faker->sentence(10),
            'price' => $this->faker->numberBetween(1000, 50000),
            'is_sold' => false,
        ];
    }
}
