# 勤怠アプリ

## 環境構築

### Dockerビルド
 1. git clone git@github.com:nyanyamarusan/kintai.git
 2. docker-compose up -d --build

＊ MySQLは、OSによって起動しない場合があるのでそれぞれのPCに合わせて docker-compose.yml ファイルを編集してください。

### Laravel環境構築

 1. docker-compose exec php bash
 2. composer install
 3. .env.exampleファイルから.envを作成し、環境変数を変更
 4. php artisan key:generate
 5. php artisan migrate
 6. php artisan db:seed

### .envファイルの設定

- ロケール設定
  - APP_LOCALE=ja
  - APP_FAKER_LOCALE=ja_JP

- セッション、キャッシュ設定
  - SESSION_DRIVER=file
  - CACHE_STORE=file

- malihog 環境変数
  - MAIL_MAILER=smtp
  - MAIL_SCHEME=null
  - MAIL_HOST=mailhog
  - MAIL_PORT=1025
  - MAIL_USERNAME=null
  - MAIL_PASSWORD=null
  - MAIL_FROM_ADDRESS="hello@example.com"
  - MAIL_FROM_NAME="${APP_NAME}"

＊ もし、変更後に設定が反映されていなかった場合、php artisan config:clear で、キャッシュクリアしてみてください。

## 使用技術・拡張

- PHP 8.3-fpm
- Laravel 12.20.0
- MySQL 8.0.41
- nginx 1.26.3
- Fortify
- mailhog
- Laravel Excel(maatwebsite/excel)
- GD拡張(Dockerfileでインストール)
- Bootstrap(CDN経由で読み込み)  
  ＊ 自作CSS('public/css/custom.css')も併用

## ER図

![ER図](/kintai.drawio.png)

## URL

- 開発環境：http://localhost/
- phpMyAdmin：http://localhost:8080/
- mailhog：http://localhost:8025/
- 一般ユーザー登録：http://localhost/register

## ログイン情報(開発用)

- 管理者
  - ログイン画面URL：http://localhost/admin/login
  - メールアドレス：admin@example.com
  - パスワード：adminpassword

- 一般ユーザー
  - ログイン画面URL：http://localhost/login
  - メールアドレス：user@example.com
  - パスワード：userpassword
