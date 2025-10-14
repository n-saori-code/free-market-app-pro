<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    // プロフィールの必要な情報が取得できる
    public function test_user_profile_displays_correct_information()
    {
        $this->seed(\Database\Seeders\UsersTableSeeder::class);
        $this->seed(\Database\Seeders\ConditionsTableSeeder::class);
        $this->seed(\Database\Seeders\ProductsTableSeeder::class);

        $user = User::where('email', 'test@example.com')->first();

        $products = Product::where('user_id', $user->id)->get();

        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $products->first()->id,
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区テスト町1-1-1',
            'building' => 'テストビル',
            'payment_method' => 'card',
        ]);

        $responseSell = $this->actingAs($user)->get('/mypage?page=sell');
        $responseSell->assertStatus(200);
        $responseSell->assertSee($user->name);
        $responseSell->assertSee($user->profile->profile_image);

        foreach ($products as $product) {
            $responseSell->assertSee($product->title);
        }

        $responseBuy = $this->actingAs($user)->get('/mypage?page=buy');
        $responseBuy->assertStatus(200);
        $responseBuy->assertSee($order->product->title);
        $responseBuy->assertSee($user->name);
        $responseBuy->assertSee($user->profile->profile_image);
    }

    // プロフィール変更項目が初期値として過去設定されていること
    public function test_profile_edit_form_displays_correct_initial_values()
    {
        $this->seed(\Database\Seeders\UsersTableSeeder::class);

        $user = User::where('email', 'test@example.com')->first();

        $response = $this->actingAs($user)->get('/mypage/profile');
        $response->assertStatus(200);

        $response->assertSee('value="' . $user->name . '"', false);
        $response->assertSee('value="' . $user->profile->postal_code . '"', false);
        $response->assertSee('value="' . $user->profile->address . '"', false);
        $response->assertSee($user->profile->profile_image);
    }
}
