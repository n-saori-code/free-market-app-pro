<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Models\Product;
use Database\Seeders\ProductsTableSeeder;

class HelloTest extends TestCase
{
    use RefreshDatabase;

    public function testHello()
    {
        // 会員登録リクエストを送信（名前を空欄にする）
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);

        // 会員登録リクエストを送信（メールアドレスを空にする）
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);

        // 会員登録リクエストを送信（パスワードを空にする）
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);

        // 会員登録リクエストを送信（パスワードを7文字にする）
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);

        // 会員登録リクエストを送信（パスワードと確認用パスワードを不一致にする）
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password321', // 不一致
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    // 認証メール
    public function test_verification_email_is_sent_after_registration()
    {
        // 会員登録後、認証メールが送信される
        Notification::fake();

        $user = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = \App\Models\User::where('email', 'test@example.com')->first();

        Notification::assertSentTo(
            [$user],
            VerifyEmail::class
        );

        // メール認証誘導画面を表示
        $this->actingAs($user);
        $response = $this->get('/email/verify');

        $response->assertStatus(200);
        $response->assertSee('認証はこちらから');
        $response->assertSee('http://localhost:8025');

        // メール認証後、プロフィール編集画面を表示
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect('/mypage/profile');
    }

    public function test_login_fails_when_email_is_missing()
    {
        // ログインリクエストを送信（メールアドレス未入力）
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);

        // ログインリクエストを送信（パスワード未入力）
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);

        // 登録されていないメールアドレスでログインを試みる
        $response = $this->post('/login', [
            'email' => 'notfound@example.com',
            'password' => 'wrongpassword',
        ]);
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);

        // 正しい情報が入力された場合、ログイン処理が実行される
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/?tab=mylist');
    }

    public function test_logout()
    {
        // ログアウトできる
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->post('/logout');
        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_all_products_displayed_from_seeder()
    {
        // シーダーを読み込んで全商品表示確認
        $user = \App\Models\User::factory()->create(['id' => 1]);
        $this->seed(\Database\Seeders\ConditionsTableSeeder::class);
        $this->seed(ProductsTableSeeder::class);

        $response = $this->get('/');
        $response->assertStatus(200);
        $products = \App\Models\Product::all();
        foreach ($products as $product) {
            $response->assertSee($product->title);
        }
    }
}
