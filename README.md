# フリーマーケットアプリ

## 環境構築

**Docker ビルド**

1. `git clone git@github.com:estra-inc/confirmation-test-contact-form.git`
2. DockerDesktop アプリを立ち上げる
3. `docker-compose up -d --build`

> _本プロジェクトでは、**M1/M2 Mac でもビルド可能** になるように `platform: linux/amd64` を指定済みです。_

```bash
# docker-compose.yml
nginx:
    platform: linux/amd64

php:
    platform: linux/amd64

mysql:
platform: linux/amd64

phpmyadmin:
platform: linux/amd64

# Dockerfile
FROM --platform=linux/amd64 php:8.1-fpm
```

**Laravel 環境構築**

1. `docker-compose exec php bash`
2. `composer install`
3. 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.env ファイルを作成
4. .env に以下の環境変数を追加

```text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

5. アプリケーションキーの作成

```bash
php artisan key:generate
```

6. マイグレーションの実行

```bash
php artisan migrate
```

7. シーディングの実行

```bash
php artisan db:seed
```

## Stripe テスト決済

1. .env に Stripe キーを設定

```bash
STRIPE_KEY=pk_test_あなたのキー
STRIPE_SECRET=sk_test_あなたのキー
```

2. 「カード払い」の場合のテストカード情報：

```bash
カード番号: 4242 4242 4242 4242
有効期限: 任意 (例: 12/34)
CVC: 任意 (例: 123)
ZIP: 任意 (例: 123-4567)
```

3. 商品購入が成功
   ・決済後、商品一覧ページで商品に「sold」表示がつく
   ・商品購入画面の購入ボタンが「sold」になり、クリックできなくなる（コーチの確認済み）

## メール認証機能(使用技術：Mailhog)

1. 会員登録後、メール認証画面に遷移
2. 「認証はこちらから」のボタンを押し、http://localhost:8025 にアクセスし、届いたメールを確認
3. メール内の「Verify Email Address」ボタンをクリック
4. 認証完了後、プロフィール設定画面に遷移

## 使用技術(実行環境)

- PHP8.1.33
- Laravel8.83.8
- MySQL8.0.26
- nginx1.21.1

## ER 図

![alt](erd.png)

## URL

- 開発環境：http://localhost/
- ユーザー登録：http://localhost/register
- phpMyAdmin：http://localhost:8080/
- Mailhog：http://localhost:8025
