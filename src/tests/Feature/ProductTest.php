<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Condition;


class ProductTest extends TestCase
{
    use RefreshDatabase;

    private function createConditionAndCategory($conditionName = '良好', $categoryName = '時計')
    {
        $condition = Condition::create(['condition_name' => $conditionName]);
        $category  = Category::create(['category_name' => $categoryName]);
        return [$condition, $category];
    }

    private function createProduct($user, $condition, $title = '商品', $isSold = false)
    {
        $product = Product::create([
            'user_id'      => $user->id,
            'condition_id' => $condition->id,
            'title'        => $title,
            'brand'        => 'BrandX',
            'description'  => '説明です',
            'price'        => 1000,
            'image'        => 'products/sample.jpg',
            'is_sold'      => $isSold,
        ]);

        return $product;
    }

    // シーダーを読み込んで全商品表示確認
    public function all_products_are_displayed()
    {
        $user = User::factory()->create();
        [$condition,] = $this->createConditionAndCategory();

        $products = [
            $this->createProduct($user, $condition, '商品1'),
            $this->createProduct($user, $condition, '商品2')
        ];

        $response = $this->get('/');
        $response->assertStatus(200);

        foreach ($products as $product) {
            $response->assertSee($product->title);
        }
    }

    // 購入済み商品は「Sold」と表示される
    public function sold_products_display_sold_label()
    {
        $user = User::factory()->create();
        [$condition,] = $this->createConditionAndCategory();

        $soldProduct = $this->createProduct($user, $condition, '腕時計', true);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('SOLD');
        $response->assertSee($soldProduct->title);
    }

    // 自分が出品した商品は表示されない
    public function user_does_not_see_own_products_on_index()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        [$condition,] = $this->createConditionAndCategory();

        $myProduct = $this->createProduct($user, $condition, '自分の商品');
        $otherProduct = $this->createProduct($otherUser, $condition, '他人の商品');

        $this->actingAs($user);
        $response = $this->get('/');

        $response->assertSee($otherProduct->title);
        $response->assertDontSee($myProduct->title);
    }

    // いいねした商品だけが表示される
    public function mylist_displays_only_favorited_products()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        [$condition,] = $this->createConditionAndCategory();

        $favProduct = $this->createProduct($user, $condition, 'いいね商品1');
        $nonFavProduct = $this->createProduct($user, $condition, 'いいねしていない商品');

        $user->favoriteProducts()->attach($favProduct->id);

        $response = $this->get('/?tab=mylist');
        $response->assertSee($favProduct->title);
        $response->assertDontSee($nonFavProduct->title);
    }

    // マイリストの購入済み商品は「Sold」と表示される
    public function test_mylist_displays_sold_label_for_purchased_products()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        [$condition,] = $this->createConditionAndCategory();

        $productSold = $this->createProduct($user, $condition, '購入済み商品', true);
        $productAvailable = $this->createProduct($user, $condition, '未購入商品', false);

        $user->favoriteProducts()->attach([$productSold->id, $productAvailable->id]);

        $response = $this->get('/?tab=mylist');
        $response->assertStatus(200);

        $response->assertSee($productSold->title);
        $response->assertSee($productAvailable->title);

        $response->assertSee('SOLD');
    }

    // マイリスト未認証の場合は何も表示されない
    public function guest_mylist_displays_nothing()
    {
        $response = $this->get('/?tab=mylist');
        $response->assertStatus(200);
        $response->assertDontSee('商品');
    }

    // 「商品名」で部分一致検索ができる
    public function test_product_search_by_partial_match()
    {
        $otherUser = User::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        [$condition,] = $this->createConditionAndCategory();

        $product1 = Product::create([
            'user_id'      => $otherUser->id,
            'condition_id' => $condition->id,
            'title'        => '腕時計',
            'brand'        => 'Rolax',
            'description'  => '高級腕時計です',
            'price'        => 10000,
            'image'        => 'products/watch.jpg',
        ]);

        $product2 = Product::create([
            'user_id'      => $otherUser->id,
            'condition_id' => $condition->id,
            'title'        => 'ネックレス',
            'brand'        => 'Tiffany',
            'description'  => '人気のアクセサリー',
            'price'        => 20000,
            'image'        => 'products/necklace.jpg',
        ]);

        $response = $this->get('/search?keyword=腕');
        $response->assertStatus(200);

        $response->assertSee('腕時計');
        $response->assertDontSee('ネックレス');
    }

    // 検索状態がマイリストでも保持されている
    public function test_search_keyword_is_preserved_on_mylist_tab_with_favorites()
    {
        $otherUser = User::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        [$condition,] = $this->createConditionAndCategory();

        $product = Product::create([
            'user_id'      => $otherUser->id,
            'condition_id' => $condition->id,
            'title'        => '腕時計',
            'brand'        => 'Rolax',
            'description'  => '高級腕時計です',
            'price'        => 10000,
            'image'        => 'products/watch.jpg',
        ]);

        $user->favoriteProducts()->attach($product->id);

        $keyword = '腕';

        $response = $this->get('/search?keyword=' . $keyword);
        $response->assertStatus(200);
        $response->assertSee('腕時計');

        $response = $this->get('/search?tab=mylist&keyword=' . $keyword);
        $response->assertStatus(200);
        $response->assertSee('腕時計');
        $response->assertSee('value="' . $keyword . '"', false);
    }

    // 商品詳細ページに必要な情報が表示される
    public function product_detail_displays_all_information()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        [$condition, $category] = $this->createConditionAndCategory();
        $product = $this->createProduct($otherUser, $condition, '腕時計');
        $product->categories()->attach($category->id);

        $response = $this->get('/item/' . $product->id);
        $response->assertStatus(200);

        $response->assertSee($product->title);
        $response->assertSee($condition->condition_name);
        $response->assertSee($category->category_name);
        $response->assertSee(asset('storage/' . $product->image));
    }

    // 複数選択されたカテゴリが表示されているか
    public function test_item_detail_page_displays_multiple_categories()
    {
        $viewer = \App\Models\User::factory()->create();
        $seller = \App\Models\User::factory()->create();

        $this->actingAs($viewer);

        $condition = \App\Models\Condition::create(['condition_name' => '良好']);

        $categories = \App\Models\Category::insert([
            ['category_name' => '時計'],
            ['category_name' => 'アクセサリー'],
            ['category_name' => '限定'],
        ]);

        $product = \App\Models\Product::create([
            'user_id' => $seller->id,
            'condition_id' => $condition->id,
            'title' => '腕時計',
            'brand' => 'Rolax',
            'description' => '高級腕時計です',
            'price' => 10000,
            'image' => 'products/watch.jpg',
        ]);

        $categoryIds = \App\Models\Category::pluck('id')->toArray();
        $product->categories()->attach($categoryIds);

        $response = $this->get('/item/' . $product->id);

        $response->assertStatus(200);

        foreach (\App\Models\Category::pluck('category_name') as $name) {
            $response->assertSee($name);
        }
    }

    // いいねアイコンを押すと、いいねした商品として登録できる。
    public function test_user_can_favorite_a_product()
    {
        $user = \App\Models\User::factory()->create();
        $seller = \App\Models\User::factory()->create();

        $this->actingAs($user);

        $condition = \App\Models\Condition::create(['condition_name' => '良好']);

        $product = \App\Models\Product::create([
            'user_id' => $seller->id,
            'condition_id' => $condition->id,
            'title' => '腕時計',
            'brand' => 'Rolax',
            'description' => '高級腕時計です',
            'price' => 10000,
            'image' => 'products/watch.jpg',
        ]);

        $this->assertEquals(0, $product->favoritedBy()->count());

        $response = $this->post('/item/' . $product->id . '/favorite');

        $response->assertStatus(200);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertEquals(1, $product->favoritedBy()->count());
    }

    // いいね追加済みのアイコンは色が変化する
    public function test_favorite_icon_changes_when_clicked()
    {
        $user = \App\Models\User::factory()->create();
        $seller = \App\Models\User::factory()->create();

        $this->actingAs($user);

        $condition = \App\Models\Condition::create(['condition_name' => '良好']);
        $category  = \App\Models\Category::create(['category_name' => '時計']);

        $product = \App\Models\Product::create([
            'user_id'      => $seller->id,
            'condition_id' => $condition->id,
            'title'        => '腕時計',
            'brand'        => 'Rolax',
            'description'  => '高級腕時計です',
            'price'        => 10000,
            'image'        => 'products/watch.jpg',
        ]);

        $product->categories()->attach($category->id);

        $response = $this->get('/item/' . $product->id);
        $response->assertStatus(200);
        $response->assertSee('icon-star.png');

        $this->post('/item/' . $product->id . '/favorite');

        $response = $this->get('/item/' . $product->id);
        $response->assertStatus(200);
        $response->assertSee('icon-star-filled.png');
    }

    // 再度いいねアイコンを押すと、いいねを解除できる。
    public function test_favorite_can_be_toggled()
    {
        $user = \App\Models\User::factory()->create();
        $seller = \App\Models\User::factory()->create();

        $this->actingAs($user);

        $condition = \App\Models\Condition::create(['condition_name' => '良好']);
        $category  = \App\Models\Category::create(['category_name' => '時計']);

        $product = \App\Models\Product::create([
            'user_id'      => $seller->id,
            'condition_id' => $condition->id,
            'title'        => '腕時計',
            'brand'        => 'Rolax',
            'description'  => '高級腕時計です',
            'price'        => 10000,
            'image'        => 'products/watch.jpg',
        ]);

        $product->categories()->attach($category->id);

        $response = $this->get('/item/' . $product->id);
        $response->assertStatus(200);
        $response->assertSee('0');
        $response->assertSee('icon-star.png');

        $this->post('/item/' . $product->id . '/favorite');

        $response = $this->get('/item/' . $product->id);
        $response->assertSee('1');
        $response->assertSee('icon-star-filled.png');

        $this->delete('/item/' . $product->id . '/favorite');

        $response = $this->get('/item/' . $product->id);
        $response->assertSee('0');
        $response->assertSee('icon-star.png');
    }

    // ログイン済みのユーザーはコメントを送信できる

    public function test_logged_in_user_can_submit_comment()
    {
        $user = \App\Models\User::factory()->create();
        $seller = \App\Models\User::factory()->create();

        $this->actingAs($user);

        $condition = \App\Models\Condition::create(['condition_name' => '良好']);
        $category  = \App\Models\Category::create(['category_name' => '時計']);

        $product = \App\Models\Product::create([
            'user_id'      => $seller->id,
            'condition_id' => $condition->id,
            'title'        => '腕時計',
            'brand'        => 'Rolax',
            'description'  => '高級腕時計です',
            'price'        => 10000,
            'image'        => 'products/watch.jpg',
        ]);

        $product->categories()->attach($category->id);

        $commentData = ['content' => '素敵な商品ですね！'];

        $response = $this->post('/products/' . $product->id . '/comments', $commentData);

        $response->assertStatus(302);
        $response->assertRedirect('/item/' . $product->id);

        $this->assertDatabaseHas('comments', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'content'    => '素敵な商品ですね！',
        ]);

        $this->assertEquals(1, $product->comments()->count());
    }

    // ログイン前のユーザーはコメントを送信できない
    public function test_guest_cannot_submit_comment()
    {
        $user = \App\Models\User::factory()->create();
        $condition = \App\Models\Condition::create(['condition_name' => '良好']);
        $category = \App\Models\Category::create(['category_name' => '時計']);

        $product = \App\Models\Product::create([
            'user_id' => $user->id,
            'condition_id' => $condition->id,
            'title' => '腕時計',
            'brand' => 'Rolax',
            'description' => '高級腕時計です',
            'price' => 10000,
            'image' => 'products/watch.jpg',
        ]);

        $product->categories()->attach($category->id);

        $response = $this->post(route('comments.store', ['product' => $product->id]), [
            'content' => '素敵な商品ですね！',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('comments', [
            'product_id' => $product->id,
            'content' => '素敵な商品ですね！',
        ]);
    }

    // コメントが入力されていない場合、バリデーションメッセージが表示される
    public function test_comment_validation_message_when_empty()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $condition = \App\Models\Condition::create(['condition_name' => '良好']);
        $category = \App\Models\Category::create(['category_name' => '時計']);

        $product = \App\Models\Product::create([
            'user_id' => $user->id,
            'condition_id' => $condition->id,
            'title' => '腕時計',
            'brand' => 'Rolax',
            'description' => '高級腕時計です',
            'price' => 10000,
            'image' => 'products/watch.jpg',
        ]);

        $product->categories()->attach($category->id);

        $response = $this->post(route('comments.store', ['product' => $product->id]), [
            'content' => '',
        ]);

        $response->assertSessionHasErrors(['content']);
    }

    // コメントが255字以上の場合、バリデーションメッセージが表示される
    public function test_comment_validation_message_when_too_long()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $condition = \App\Models\Condition::create(['condition_name' => '良好']);
        $category = \App\Models\Category::create(['category_name' => '時計']);

        $product = \App\Models\Product::create([
            'user_id' => $user->id,
            'condition_id' => $condition->id,
            'title' => '腕時計',
            'brand' => 'Rolax',
            'description' => '高級腕時計です',
            'price' => 10000,
            'image' => 'products/watch.jpg',
        ]);

        $product->categories()->attach($category->id);

        $longComment = str_repeat('あ', 256);

        $response = $this->post(route('comments.store', ['product' => $product->id]), [
            'content' => $longComment,
        ]);

        $response->assertSessionHasErrors(['content']);
    }

    // 商品出品画面にて必要な情報が保存できること
    public function user_can_create_a_product()
    {
        $this->seed(\Database\Seeders\UsersTableSeeder::class);
        $this->seed(\Database\Seeders\CategoriesTableSeeder::class);

        $user = User::where('email', 'test@example.com')->first();

        $productData = [
            'title' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => '商品の説明です。',
            'price' => 1000,
            'condition_id' => 1,
            'categories' => Category::pluck('id')->take(2)->toArray(),
            'image' => null,
        ];

        $response = $this->actingAs($user)->post('/sell', $productData);

        $response->assertStatus(302);
        $response->assertRedirect('/mypage');

        $this->assertDatabaseHas('products', [
            'title' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => '商品の説明です。',
            'price' => 1000,
            'condition_id' => 1,
            'user_id' => $user->id,
        ]);

        $product = Product::where('title', 'テスト商品')->first();
        foreach ($productData['categories'] as $categoryId) {
            $this->assertDatabaseHas('category_product', [
                'product_id' => $product->id,
                'category_id' => $categoryId,
            ]);
        }
    }
}
