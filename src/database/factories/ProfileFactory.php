<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class ProfileFactory extends Factory
{
    protected $model = \App\Models\Profile::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'profile_image' => 'profile_images/default.jpg',
            'postal_code' => $this->faker->postcode(),
            'address' => $this->faker->prefecture() . $this->faker->city() . $this->faker->streetAddress(),
            'building' => $this->faker->optional()->secondaryAddress(),
        ];
    }
}
