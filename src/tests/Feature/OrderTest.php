<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Order;
use App\Models\Profile;
use App\Services\StripeService;
use Mockery;

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

        Profile::factory()->create([
            'user_id'     => $this->user->id,
            'postal_code' => '123-4567',
            'address'     => '東京都新宿区',
            'building'    => 'テストビル',
        ]);

        $this->condition = Condition::create(['condition_name' => '良好']);
        $this->category  = Category::create(['category_name' => '時計']);
    }

    // 「購入する」ボタンを押下すると購入が完了する
    public function test_user_can_purchase_product()
    {
        $product = Product::factory()->create([
            'user_id'      => $this->user->id,
            'condition_id' => $this->condition->id,
            'price'        => 10000,
            'is_sold'      => false,
        ]);

        $product->categories()->attach($this->category->id);

        $mockStripe = Mockery::mock(StripeService::class);
        $mockStripe->shouldReceive('createCheckoutSession')
            ->once()
            ->andReturn((object)[
                'url' => 'https://checkout.stripe.com/test-session',
            ]);

        $this->app->instance(StripeService::class, $mockStripe);

        $this->get(route('purchase.show', $product->id))
            ->assertStatus(200)
            ->assertSee('購入する');

        $response = $this->post(route('purchase.store', $product->id), [
            'payment_method' => 'credit_card',
            'postal_code'    => '123-4567',
            'address'        => '東京都新宿区',
            'building'       => 'テストビル',
        ]);

        $response->assertRedirect('https://checkout.stripe.com/test-session');

        $this->get(route('purchase.success', $product->id))
            ->assertRedirect('/');

        $this->assertDatabaseHas('products', [
            'id'      => $product->id,
            'is_sold' => true,
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id'        => $this->user->id,
            'product_id'     => $product->id,
            'payment_method' => 'stripe',
            'postal_code'    => '123-4567',
            'address'        => '東京都新宿区',
            'building'       => 'テストビル',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // 購入した商品は商品一覧画面にて「sold」と表示される
    public function test_purchased_product_is_marked_as_sold_on_product_list()
    {
        $seller = User::factory()->create();

        $product = Product::factory()->create([
            'user_id'      => $seller->id,
            'condition_id' => $this->condition->id,
            'price'        => 10000,
            'is_sold'      => false,
        ]);

        $product->categories()->attach($this->category->id);

        $mockStripe = \Mockery::mock(StripeService::class);
        $mockStripe->shouldReceive('createCheckoutSession')
            ->once()
            ->andReturn((object)[
                'url' => 'https://checkout.stripe.com/test-session',
            ]);

        $this->app->instance(StripeService::class, $mockStripe);

        $this->get(route('purchase.show', $product->id))
            ->assertStatus(200);

        $this->post(route('purchase.store', $product->id), [
            'payment_method' => 'credit_card',
            'postal_code'    => '123-4567',
            'address'        => '東京都新宿区',
            'building'       => 'テストビル',
        ])->assertRedirect('https://checkout.stripe.com/test-session');

        $this->get(route('purchase.success', $product->id))
            ->assertRedirect('/');

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('SOLD');
    }

    // 「プロフィール/購入した商品一覧」に追加されている
    public function test_purchased_product_is_displayed_on_profile_purchase_list()
    {
        $seller = User::factory()->create();

        $product = Product::factory()->create([
            'user_id'      => $seller->id,
            'condition_id' => $this->condition->id,
            'price'        => 10000,
            'is_sold'      => false,
        ]);

        $product->categories()->attach($this->category->id);

        $mockStripe = \Mockery::mock(StripeService::class);
        $mockStripe->shouldReceive('createCheckoutSession')
            ->once()
            ->andReturn((object)[
                'url' => 'https://checkout.stripe.com/test-session',
            ]);

        $this->app->instance(StripeService::class, $mockStripe);

        $this->get(route('purchase.show', $product->id))
            ->assertStatus(200);

        $this->post(route('purchase.store', $product->id), [
            'payment_method' => 'credit_card',
            'postal_code'    => '123-4567',
            'address'        => '東京都新宿区',
            'building'       => 'テストビル',
        ])->assertRedirect('https://checkout.stripe.com/test-session');

        $this->get(route('purchase.success', $product->id))
            ->assertRedirect('/');

        $response = $this->get(route('mypage'));

        $response->assertStatus(200)
            ->assertSee($product->title);
    }

    // 小計画面で変更が反映される
    public function test_payment_method_select_box_is_rendered()
    {
        $product = Product::factory()->create([
            'condition_id' => $this->condition->id,
            'is_sold' => false,
        ]);

        $product->categories()->attach($this->category->id);

        $this->actingAs($this->user)
            ->get(route('purchase.show', $product->id))
            ->assertStatus(200)
            ->assertSee('支払い方法')
            ->assertSee('コンビニ払い')
            ->assertSee('カード払い')
            ->assertSee('未選択');
    }

    // 送付先住所変更画面にて登録した住所が商品購入画面に反映されている
    public function test_updated_shipping_address_is_reflected_on_purchase_page()
    {
        $this->actingAs($this->user);

        $product = Product::factory()->create([
            'user_id'      => $this->user->id,
            'condition_id' => $this->condition->id,
            'is_sold'      => false,
        ]);

        $product->categories()->attach($this->category->id);

        $this->get(route('address.show', ['item_id' => $product->id]))
            ->assertStatus(200);

        $this->post(route('address.update', ['item_id' => $product->id]), [
            'postal_code' => '123-4567',
            'address'     => '東京都渋谷区テスト町1-2-3',
            'building'    => 'テストマンション101',
        ])->assertRedirect(route('purchase.show', $product->id));

        $response = $this->get(route('purchase.show', $product->id));

        $response->assertStatus(200)
            ->assertSee('〒123-4567')
            ->assertSee('東京都渋谷区テスト町1-2-3')
            ->assertSee('テストマンション101');
    }

    // 購入した商品に送付先住所が紐づいて登録される
    public function test_shipping_address_is_saved_with_purchased_product()
    {
        $this->actingAs($this->user);

        $seller = User::factory()->create();

        $product = Product::factory()->create([
            'user_id'      => $seller->id,
            'condition_id' => $this->condition->id,
            'is_sold'      => false,
        ]);

        $product->categories()->attach($this->category->id);

        $this->post(route('address.update', ['item_id' => $product->id]), [
            'postal_code' => '123-4567',
            'address'     => '東京都渋谷区テスト町1-2-3',
            'building'    => 'テストマンション101',
        ]);

        $this->get(route('purchase.success', $product->id))
            ->assertRedirect('/');

        $this->assertDatabaseHas('orders', [
            'user_id'     => $this->user->id,
            'product_id'  => $product->id,
            'postal_code' => '123-4567',
            'address'     => '東京都渋谷区テスト町1-2-3',
            'building'    => 'テストマンション101',
        ]);

        $this->assertDatabaseHas('products', [
            'id'      => $product->id,
            'is_sold' => true,
        ]);
    }
}
