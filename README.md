# フリーマーケットアプリ

## 環境構築

**Docker ビルド**

1. リポジトリをクローン
   `git clone git@github.com:n-saori-code/nakanosaori-kadai-1.git`

2. DockerDesktop アプリを立ち上げる

3. クローンしたディレクトリ内に移動し、以下のコマンドで Docker コンテナをビルドして起動します
   `docker-compose up -d --build`

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

1. コンテナに入る
   `docker-compose exec php bash`

2. 依存パッケージをインストール
   `composer install`

3. 「.env.example」ファイルを「.env」ファイルにコピーまたはリネーム
   `cp .env.example .env`

4. .env に以下の環境変数を追加

```text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

```text
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="${APP_NAME}"
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

8. ストレージリンクの作成（画像表示のため）

```bash
php artisan storage:link
```

## Stripe テスト決済の設定

1. [Stripe 公式サイト](https://stripe.com/jp) にアクセスし、テストモードでアカウントを登録します。
   管理画面の「開発者」→「API キー」から以下のキーを取得します。

2. `.env` に取得した Stripe の API キーを設定します。

```bash
STRIPE_KEY=pk_test_***************
STRIPE_SECRET=sk_test_***************
```

3. 支払い方法

「カード払い」の場合のテストカード情報：

```bash
カード番号: 4242 4242 4242 4242
有効期限: 任意 (例: 12/34)
CVC: 任意 (例: 123)
ZIP: 任意 (例: 123-4567)
```

「コンビニ払い」の場合は、必要事項を入力し支払い先を選択できます。
（※テストモードのため、実際の支払い処理までは進みません。）

4. 商品購入が成功
   ・決済後、商品一覧ページに遷移し、商品に「sold」表示がつきます。

**※出品した商品・購入済み商品ページの挙動は要件に明記がなかったため、コーチと相談の上、以下のように実装しています。**

- 詳細画面の「購入手続きへ」ボタンが「SOLD」となり、クリックできなくなります。
- コメント送信ボタンも無効化され、「売り切れの為、コメントできません。」と表示されます。
- 出品した商品の詳細画面では、ボタンが「出品者のため購入できません」となり、クリックできません。

## メール認証機能(使用技術：Mailhog)

1. 会員登録後、メール認証画面に遷移
2. 「認証はこちらから」のボタンを押し、http://localhost:8025 にアクセスし、届いたメールを確認
3. メール内の「Verify Email Address」ボタンをクリック
4. 認証完了後、プロフィール設定画面に遷移

## 初期ログインアカウント（シーディングで自動作成）

php artisan db:seed 実行後、以下のアカウントが自動的に登録されます。

### 管理者アカウント

- ユーザー名：管理者ユーザー
- メールアドレス：admin@example.com
- パスワード：adminpassword

### 一般ユーザーアカウント

- ユーザー名：一般ユーザー
- メールアドレス：user@example.com
- パスワード：userpassword

## 使用技術(実行環境)

- PHP8.1.33
- Laravel8.83.8
- MySQL8.0.26
- nginx1.21.1

## ER 図

![alt](erd.png?251010)

## URL

- 開発環境：http://localhost/
- ユーザー登録：http://localhost/register
- phpMyAdmin：http://localhost:8080/
- Mailhog：http://localhost:8025
