# Laravel_Pigry（体重管理アプリ）

## 環境構築
- Docker のビルドからマイグレーション、シーディングまでを行い開発環境を構築
- `docker-compose up -d --build` コンテナが作成
- `docker-compose exec php bash` PHPコンテナ内にログイン
- `composer install` をインストール
- `cp .env.example .env` ファイルをコピー(`.env`作成)
- `.env` の設定変更
- `php artisan key:generate`  アプリキー生成
- `php artisan migrate --seed` によりデータベースをセットアップ  
- `php artisan serve` でローカルサーバー起動
- --
- `login-email` test@example.com
- `login-password` password

## 使用技術（実行環境）
- PHP 8.x
- Laravel 8.x
- MySQL 8.x
- WSL2 + Docker（開発環境）

## ER図
<img width="681" height="306" alt="drawio" src="https://github.com/user-attachments/assets/ef11722e-80b2-495f-aef2-d8f052d98203" />



## URL
- 新規会員登録: http://localhost/register
- ログイン: http://localhost/login
- 管理画面: http://localhost/weight_logs
