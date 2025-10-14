<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Order;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Condition $condition;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->condition = Condition::create(['condition_name' => '良好']);
        $this->category  = Category::create(['category_name' => '時計']);
    }

    // 「購入する」ボタンを押下すると購入が完了する（Stripe決済ページにリダイレクトされる）
    public function test_purchase_button_redirects_to_payment_page_with_mock()
    {
        $product = $this->createProduct($this->user);

        $this->mockStripeService('https://checkout.stripe.com/test-session');

        $response = $this->get("/purchase/{$product->id}");
        $response->assertStatus(200)->assertSee('購入する');

        $purchaseResponse = $this->post("/purchase/{$product->id}", [
            'payment_method' => 'credit_card',
            'postal_code'    => '123-4567',
            'address'        => '東京都新宿区',
            'building'       => 'テストビル',
        ]);

        $purchaseResponse->assertRedirect('https://checkout.stripe.com/test-session');

        $this->assertDatabaseHas('orders', [
            'user_id'        => $this->user->id,
            'product_id'     => $product->id,
            'payment_method' => 'credit_card',
            'postal_code'    => '123-4567',
            'address'        => '東京都新宿区',
            'building'       => 'テストビル',
        ]);
    }

    // 購入した商品は商品一覧画面にて「sold」と表示される
    public function test_purchased_product_shows_sold_in_list()
    {
        $seller = User::factory()->create();
        $product = $this->createProduct($seller);

        $this->mockStripeService('https://checkout.stripe.com/test-session');

        $this->post("/purchase/{$product->id}", [
            'payment_method' => 'credit_card',
            'postal_code'    => '123-4567',
            'address'        => '東京都新宿区',
            'building'       => 'テストビル',
        ]);

        $this->get(route('purchase.success', ['item_id' => $product->id]));
        $this->assertTrue(Product::find($product->id)->is_sold);

        $response = $this->get('/');
        $response->assertStatus(200)->assertSee('SOLD');
    }

    // 「プロフィール/購入した商品一覧」に追加されている
    public function test_purchased_product_appears_in_profile()
    {
        $buyer = User::factory()->create();
        $this->actingAs($buyer);

        $seller = User::factory()->create();
        $product = $this->createProduct($seller);

        $this->mockStripeService('https://checkout.stripe.com/test-session');

        $this->post("/purchase/{$product->id}", [
            'payment_method' => 'credit_card',
            'postal_code'    => '123-4567',
            'address'        => '東京都新宿区',
            'building'       => 'テストビル',
        ]);

        $this->get(route('purchase.success', ['item_id' => $product->id]));
        $this->assertTrue(Product::find($product->id)->is_sold);

        $response = $this->get('/mypage?page=buy');
        $response->assertStatus(200)->assertSee('腕時計');
    }

    // 小計画面で変更が反映される（バリテーションで正しく選択されているか判定）
    public function test_payment_method_selection_reflects_old_value()
    {
        $product = $this->createProduct($this->user);

        $response = $this->get("/purchase/{$product->id}");
        $response->assertStatus(200)
            ->assertSee('<option value="" disabled selected hidden>選択してください</option>', false);

        $this->from("/purchase/{$product->id}")
            ->post("/purchase/{$product->id}", [
                'payment_method' => 'credit_card',
                'postal_code'    => '',
                'address'        => '',
                'building'       => '',
            ])
            ->assertRedirect("/purchase/{$product->id}");

        $response = $this->get("/purchase/{$product->id}");
        $response->assertSee('<option value="credit_card" selected>カード払い</option>', false)
            ->assertDontSee('<option value="convenience_store" selected>コンビニ払い</option>', false);
    }

    // 送付先住所変更画面にて登録した住所が商品購入画面に反映されている
    public function test_address_change_reflects_in_purchase_screen()
    {
        $this->user->profile()->create([
            'postal_code' => '000-0000',
            'address'     => 'テスト区',
            'building'    => '旧ビル',
        ]);

        $product = $this->createProduct($this->user);

        $response = $this->post("/purchase/address/{$product->id}", [
            'postal_code' => '123-4567',
            'address'     => '東京都新宿区',
            'building'    => 'テストビル',
        ]);

        $response->assertStatus(302)->assertRedirect(route('purchase.show', $product->id));

        $this->assertDatabaseHas('orders', [
            'user_id'     => $this->user->id,
            'product_id'  => $product->id,
            'postal_code' => '123-4567',
            'address'     => '東京都新宿区',
            'building'    => 'テストビル',
        ]);

        $response = $this->get("/purchase/{$product->id}");
        $response->assertStatus(200)
            ->assertSee('123-4567')
            ->assertSee('東京都新宿区')
            ->assertSee('テストビル')
            ->assertSee('name="postal_code" value="123-4567"', false)
            ->assertSee('name="address" value="東京都新宿区"', false)
            ->assertSee('name="building" value="テストビル"', false);
    }

    // 購入した商品に送付先住所が紐づいて登録される
    public function test_order_saves_shipping_address_correctly()
    {
        $this->user->profile()->create([
            'postal_code' => '000-0000',
            'address'     => '旧住所',
            'building'    => '旧ビル',
        ]);

        $product = $this->createProduct($this->user);

        $this->post("/purchase/address/{$product->id}", [
            'postal_code' => '123-4567',
            'address'     => '東京都新宿区',
            'building'    => 'ビル101',
        ])->assertStatus(302);

        $this->post("/purchase/{$product->id}", [
            'payment_method' => 'credit_card',
            'postal_code'    => '123-4567',
            'address'        => '東京都新宿区',
            'building'       => 'ビル101',
        ])->assertStatus(302);

        $this->assertDatabaseHas('orders', [
            'user_id'        => $this->user->id,
            'product_id'     => $product->id,
            'postal_code'    => '123-4567',
            'address'        => '東京都新宿区',
            'building'       => 'ビル101',
            'payment_method' => 'credit_card',
        ]);
    }

    /**
     * 商品作成の共通処理
     */
    protected function createProduct(User $owner): Product
    {
        $product = Product::create([
            'user_id'      => $owner->id,
            'condition_id' => $this->condition->id,
            'title'        => '腕時計',
            'brand'        => 'Rolax',
            'description'  => '高級腕時計です',
            'price'        => 10000,
            'image'        => 'products/watch.jpg',
        ]);
        $product->categories()->attach($this->category->id);
        return $product;
    }

    /**
     * Stripeサービスをモック化する
     */
    protected function mockStripeService(string $redirectUrl): void
    {
        $mockSession = (object)['url' => $redirectUrl];
        $this->mock(\App\Services\StripeService::class, function ($mock) use ($mockSession) {
            $mock->shouldReceive('createCheckoutSession')
                ->andReturn($mockSession);
        });
    }
}
