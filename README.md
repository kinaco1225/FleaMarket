# FleaMarket（フリーマーケットアプリ）

## 環境構築
### Docker のビルドからマイグレーション、シーディングまでを行い開発環境を構築
- `docker-compose up -d --build` コンテナが作成
- `docker-compose exec php bash` PHPコンテナ内にログイン
- `composer install` をインストール
- `cp .env.example .env` ファイルをコピー(`.env`作成)
- `.env` の設定変更
- `php artisan key:generate`  アプリキー生成
- `php artisan migrate --seed` によりデータベースをセットアップ  
- `php artisan serve` でローカルサーバー起動
- -- 
## .env 設定について（Stripe）
#### 本アプリでは Stripe を使用していますが、開発・テスト環境ではダミーキーを使用します。以下のように `.env`　`.env.testing` に設定してください。
- STRIPE_KEY=pk_test_xxxxxxxxxxxxxx
- STRIPE_SECRET=your_stripe_test_secret
- -- 
## 画像アップロードに関する権限設定
#### プロフィール画像・商品画像は storage ディレクトリに保存されます。Docker 環境では、以下のコマンドで 書き込み権限の設定を行ってください。
- chmod -R 777 storage bootstrap/cache
- php artisan storage:link
- --
## テスト用ユーザー情報
#### 本アプリには 動作確認用のテストユーザーを2名用意しています。いずれも 同一のパスワードでログイン可能です。
##### テストユーザー①
- `login-email` test1@example.com
- `login-password` password
##### テストユーザー②
- `login-email` test2@example.com
- `login-password` password

## 使用技術（実行環境）
- PHP 8.x
- Laravel 8.x
- MySQL 8.x
- WSL2 + Docker（開発環境）

##補足事項
-支払い方法の選択機能については、画面上の表示制御をJavaScriptで行っているため、バックエンド側での個別実装は行っていません。

## ER図
<img width="660" height="921" alt="er_diagram" src="https://github.com/user-attachments/assets/9ad8fba6-2070-4174-96ee-079adc2c1897" />

## URL
- 新規会員登録: http://localhost/register
- ログイン: http://localhost/login
- 商品一覧（トップ）: http://localhost/
