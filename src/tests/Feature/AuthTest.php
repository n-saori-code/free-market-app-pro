<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;
use App\Models\User;


class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function validRegistrationData(array $overrides = [])
    {
        return array_merge([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    // 会員登録リクエストを送信（名前を空欄にする）
    public function test_name_is_required()
    {
        $response = $this->post('/register', $this->validRegistrationData(['name' => '']));
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    // 会員登録リクエストを送信（メールアドレスを空にする）
    public function test_email_is_required()
    {
        $response = $this->post('/register', $this->validRegistrationData(['email' => '']));
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    // 会員登録リクエストを送信（パスワードを空にする）
    public function test_password_is_required()
    {
        $response = $this->post('/register', $this->validRegistrationData(['password' => '', 'password_confirmation' => '']));
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    // 会員登録リクエストを送信（パスワードを7文字にする）
    public function test_password_must_be_min_8_chars()
    {
        $response = $this->post('/register', $this->validRegistrationData(['password' => 'pass123', 'password_confirmation' => 'pass123']));
        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    // 会員登録リクエストを送信（パスワードと確認用パスワードを不一致にする）
    public function test_password_confirmation_must_match()
    {
        $response = $this->post('/register', $this->validRegistrationData(['password_confirmation' => 'different123']));
        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    // ------------------------------
    // 認証メール
    // ------------------------------
    // 会員登録後、認証メールが送信される
    public function test_verification_email_is_sent_after_registration()
    {
        Notification::fake();

        $this->post('/register', $this->validRegistrationData());
        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo([$user], VerifyEmail::class);
    }


    // メール認証誘導画面を表示
    public function test_email_verification_page_displays_correctly()
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        $response = $this->get('/email/verify');
        $response->assertStatus(200);
        $response->assertSee('認証はこちらから');
        $response->assertSee(config('app.url'));
    }

    // メール認証後、プロフィール編集画面を表示
    public function test_user_can_verify_email_and_redirect()
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('profile.edit'));
    }

    // ------------------------------
    // ログインバリデーション
    // ------------------------------
    // ログインリクエストを送信（メールアドレス未入力）
    public function test_login_fails_when_email_is_missing()
    {
        $response = $this->post('/login', ['email' => '', 'password' => 'password123']);
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    // ログインリクエストを送信（パスワード未入力）
    public function test_login_fails_when_password_is_missing()
    {
        $response = $this->post('/login', ['email' => 'test@example.com', 'password' => '']);
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    // 登録されていないメールアドレスでログインを試みる
    public function test_login_fails_with_unregistered_email()
    {
        $response = $this->post('/login', ['email' => 'notfound@example.com', 'password' => 'wrongpassword']);
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

    // 正しい情報が入力された場合、ログイン処理が実行される
    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', ['email' => 'test@example.com', 'password' => 'password123']);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/?tab=mylist');
    }

    // ------------------------------
    // ログアウト
    // ------------------------------
    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
