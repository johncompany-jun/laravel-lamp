# エックスサーバーへのデプロイ手順

このドキュメントは、Laravelアプリケーションをエックスサーバーのレンタルサーバーにデプロイする手順を説明します。

## 前提条件

- エックスサーバーのアカウント（スタンダードプラン以上推奨）
- SSH接続が有効になっていること
- PHP 8.3が利用可能であること
- MySQLデータベースが作成されていること

## 1. 事前準備

### 1.1 エックスサーバー側の設定

#### データベースの作成
1. エックスサーバーのサーバーパネルにログイン
2. 「MySQL設定」から新しいデータベースを作成
3. データベース名、ユーザー名、パスワードをメモ

#### SSH接続の有効化
1. サーバーパネル → 「SSH設定」
2. SSH接続を「ON」にする
3. 公開鍵認証設定（推奨）

#### PHP バージョンの確認
1. サーバーパネル → 「PHP Ver.切替」
2. ドメインに対してPHP 8.3を選択

### 1.2 ローカル環境での準備

#### 本番用の.envファイルを準備

```bash
# .env.production として保存（後でサーバー上で.envにリネーム）
APP_NAME="イベント管理システム"
APP_ENV=production
APP_KEY=                    # デプロイ後に生成
APP_DEBUG=false
APP_TIMEZONE=Asia/Tokyo
APP_URL=https://your-domain.com

APP_LOCALE=ja
APP_FALLBACK_LOCALE=ja
APP_FAKER_LOCALE=ja_JP

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

# エックスサーバーのデータベース情報
DB_CONNECTION=mysql
DB_HOST=mysqlXXX.xserver.jp    # エックスサーバーのMySQLホスト
DB_PORT=3306
DB_DATABASE=your_database_name  # 作成したデータベース名
DB_USERNAME=your_db_user        # データベースユーザー名
DB_PASSWORD=your_db_password    # データベースパスワード

SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=database

# メール設定（エックスサーバーのSMTP）
MAIL_MAILER=smtp
MAIL_HOST=your-domain.com
MAIL_PORT=465
MAIL_USERNAME=your-email@your-domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"

QUEUE_CONNECTION=database
```

### 1.3 フロントエンドアセットのビルド

**重要**: 本番環境ではViteの開発サーバーは動作しません。デプロイ前にアセットをビルドする必要があります。

```bash
# ローカル環境で実行
# Node.jsとnpmがインストールされていることを確認
node -v
npm -v

# 依存関係をインストール（初回のみ）
npm install

# 本番用にビルド
npm run build
```

ビルドが完了すると、`public/build/` ディレクトリにCSS/JavaScriptファイルが生成されます。

**注意**: `public/build/` ディレクトリも必ずサーバーにアップロードしてください。これがないとCSSやJavaScriptが読み込まれません。

## 2. ファイルのアップロード

### 2.1 SSHでサーバーに接続

```bash
ssh your-account@your-server.xserver.jp
```

### 2.2 ディレクトリ構成

エックスサーバーでは、以下の構成を推奨します：

```
/home/your-account/
├── your-domain.com/
│   └── public_html/          # ドキュメントルート（ここにLaravelのpublicフォルダの内容を配置）
└── laravel-app/              # Laravelのアプリケーションフォルダ（publicフォルダ以外）
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    └── .env
```

### 2.3 アップロード方法

#### 方法1: Git経由（推奨）

1. サーバー上でGitリポジトリをクローン

```bash
cd /home/your-account/
git clone https://github.com/your-repo/laravel-app.git
```

#### 方法2: SFTP/SCP経由

1. FileZillaやCyberduckなどのSFTPクライアントを使用
2. ローカルのプロジェクトファイルをサーバーにアップロード
3. `node_modules`、`.git`、`.env`は除外

```bash
# ローカルから実行
scp -r ./src/* your-account@your-server.xserver.jp:/home/your-account/laravel-app/
```

## 3. サーバー上での設定

### 3.1 SSH接続してサーバー上で作業

```bash
ssh your-account@your-server.xserver.jp
cd /home/your-account/laravel-app
```

### 3.2 Composerのインストール

エックスサーバーにはComposerがインストールされていない場合があります。

```bash
# Composerをダウンロード
curl -sS https://getcomposer.org/installer | php

# 依存関係をインストール（本番環境用）
php composer.phar install --no-dev --optimize-autoloader
```

### 3.3 環境設定ファイルの作成

```bash
# .env.productionを.envにコピー（またはローカルから転送した.envをリネーム）
cp .env.production .env

# アプリケーションキーを生成
php artisan key:generate
```

### 3.4 ストレージのパーミッション設定

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 3.5 データベースのマイグレーション

```bash
# マイグレーション実行
php artisan migrate --force

# シーダー実行（必要に応じて）
php artisan db:seed --force
```

### 3.6 キャッシュとビューの最適化

```bash
# 設定キャッシュ
php artisan config:cache

# ルートキャッシュ
php artisan route:cache

# ビューキャッシュ
php artisan view:cache

# オートローダー最適化（composer実行時に既に実施済み）
```

## 4. 公開ディレクトリの設定

### 4.1 シンボリックリンクの作成

Laravelの`public`フォルダの内容を公開ディレクトリに配置します。

```bash
# 既存のpublic_htmlを削除（バックアップ推奨）
cd /home/your-account/your-domain.com/
rm -rf public_html

# Laravelのpublicフォルダへのシンボリックリンクを作成
ln -s /home/your-account/laravel-app/public public_html
```

### 4.2 ストレージへのシンボリックリンク

```bash
cd /home/your-account/laravel-app
php artisan storage:link
```

### 4.3 .htaccessの確認

`public/.htaccess`が正しく配置されていることを確認してください。

## 5. エックスサーバー特有の設定

### 5.1 PHP設定の調整

サーバーパネルから「php.ini設定」で以下を確認：

- `memory_limit`: 256M以上推奨
- `max_execution_time`: 60以上
- `post_max_size`: 20M以上
- `upload_max_filesize`: 20M以上

### 5.2 .htaccessの追加設定（必要に応じて）

`public/.htaccess`に以下を追加することで、PHPバージョンを明示できます：

```apache
# PHP 8.3を使用（エックスサーバー特有）
AddHandler application/x-httpd-php8.3 .php
```

## 6. 動作確認

### 6.1 アクセステスト

ブラウザで`https://your-domain.com`にアクセスし、アプリケーションが正しく表示されることを確認します。

### 6.2 確認項目

- [ ] トップページが表示される
- [ ] ログインができる
- [ ] 新規登録ができる
- [ ] イベント一覧が表示される
- [ ] イベント申込ができる
- [ ] 管理画面にアクセスできる
- [ ] CSS/JSが正しく読み込まれている

### 6.3 エラーが発生した場合

```bash
# ログを確認
tail -f storage/logs/laravel.log

# キャッシュをクリア
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 再度キャッシュを生成
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 7. メンテナンス

### 7.1 アップデート手順

```bash
# SSH接続
ssh your-account@your-server.xserver.jp
cd /home/your-account/laravel-app

# メンテナンスモード開始
php artisan down

# Gitで最新版を取得（Git使用の場合）
git pull origin main

# 依存関係を更新
php composer.phar install --no-dev --optimize-autoloader

# マイグレーション実行
php artisan migrate --force

# キャッシュクリア＆再生成
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# メンテナンスモード終了
php artisan up
```

### 7.2 バックアップ

#### データベースのバックアップ

```bash
# mysqldumpでバックアップ
mysqldump -h mysqlXXX.xserver.jp -u your_db_user -p your_database_name > backup_$(date +%Y%m%d).sql
```

#### ファイルのバックアップ

```bash
# アップロードファイルのバックアップ
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public
```

## 8. セキュリティ対策

### 8.1 .envファイルの保護

`.env`ファイルが外部からアクセスできないことを確認：

```bash
# .htaccessに追加（通常は不要だが念のため）
<Files .env>
    Order allow,deny
    Deny from all
</Files>
```

### 8.2 管理画面の保護

必要に応じてBasic認証やIP制限を設定してください。

## 9. トラブルシューティング

### 問題: 500 Internal Server Error

**原因**: パーミッション、.htaccess、PHPバージョンの問題

**解決策**:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
php artisan config:clear
```

### 問題: CSS/JSが読み込まれない

**原因**: APP_URLの設定ミス、または公開ディレクトリの設定ミス

**解決策**:
- `.env`の`APP_URL`を確認
- `public`フォルダのシンボリックリンクを確認

### 問題: データベース接続エラー

**原因**: データベース設定の誤り

**解決策**:
- `.env`のDB_*の設定を再確認
- エックスサーバーのMySQLホスト名を確認（`mysqlXXX.xserver.jp`形式）

### 問題: メール送信できない

**原因**: SMTP設定の誤り

**解決策**:
- エックスサーバーのメールアカウントを作成
- `.env`のMAIL_*設定を確認
- `MAIL_ENCRYPTION=ssl`、`MAIL_PORT=465`を使用

## 10. パフォーマンス最適化

### 10.1 OPcacheの確認

エックスサーバーではOPcacheが有効になっています。サーバーパネルで確認してください。

### 10.2 データベースインデックス

適切なインデックスが設定されていることを確認：

```sql
-- 主要なインデックスの確認
SHOW INDEX FROM events;
SHOW INDEX FROM event_applications;
SHOW INDEX FROM event_assignments;
```

### 10.3 クエリの最適化

N+1問題が発生していないか確認：

```bash
# ログでクエリ数を確認
tail -f storage/logs/laravel.log
```

## まとめ

以上の手順で、エックスサーバーへのデプロイが完了します。

本番運用を開始する前に、必ず以下を確認してください：

- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] 適切なエラーログ設定
- [ ] データベースバックアップの定期実行
- [ ] SSL証明書の設定（Let's Encrypt等）

問題が発生した場合は、`storage/logs/laravel.log`を確認してください。
