<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Profile;
use App\Models\Condition;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // 条件データ（存在しなければ作成）
        $conditions = [
            '良好',
            '目立った傷や汚れなし',
            'やや傷や汚れあり',
            '状態が悪い',
        ];
        foreach ($conditions as $name) {
            Condition::firstOrCreate(['condition_name' => $name]);
        }

        // ユーザー作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // プロフィール作成
        Profile::factory()->create([
            'user_id' => $user->id,
        ]);

        // 商品作成（条件1を紐付け）
        Product::factory()->create([
            'user_id' => $user->id,
            'condition_id' => Condition::first()->id,
        ]);
    }

    // プロフィールの必要な情報が取得できる
    public function test_user_profile_displays_correct_information()
    {
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user, 'ユーザーが取得できませんでした。');

        $product = Product::where('user_id', $user->id)->first();

        // 注文作成
        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区テスト町1-1-1',
            'building' => 'テストビル',
            'payment_method' => 'card',
        ]);

        // 販売ページ確認
        $responseSell = $this->actingAs($user)->get('/mypage?page=sell');
        $responseSell->assertStatus(200);
        $responseSell->assertSee($user->name);
        $responseSell->assertSee($user->profile->profile_image);
        $responseSell->assertSee($product->title);

        // 購入ページ確認
        $responseBuy = $this->actingAs($user)->get('/mypage?page=buy');
        $responseBuy->assertStatus(200);
        $responseBuy->assertSee($order->product->title);
        $responseBuy->assertSee($user->name);
        $responseBuy->assertSee($user->profile->profile_image);
    }

    // プロフィール変更項目が初期値として過去設定されていること
    public function test_profile_edit_form_displays_correct_initial_values()
    {
        $user = User::where('email', 'test@example.com')->first();

        $response = $this->actingAs($user)->get('/mypage/profile');
        $response->assertStatus(200);

        $response->assertSee('value="' . $user->name . '"', false);
        $response->assertSee('value="' . $user->profile->postal_code . '"', false);
        $response->assertSee('value="' . $user->profile->address . '"', false);
        $response->assertSee($user->profile->profile_image);
    }
}
