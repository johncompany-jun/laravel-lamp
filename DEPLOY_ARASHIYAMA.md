# arashiyama-cong-map.net へのデプロイ手順

このドキュメントは、`arashiyama-cong-map.net` ドメインへのデプロイ専用の手順書です。

## 1. エックスサーバー側の準備

### 1.1 データベース作成

1. エックスサーバーのサーバーパネルにログイン
2. 「MySQL設定」を開く
3. 「MySQL追加」タブで新しいデータベースを作成
   - 推奨データベース名: `arashiyama_events` など
4. 「MySQLユーザ追加」タブでユーザーを作成
5. 「MySQL一覧」タブでユーザーにデータベースへのアクセス権を付与
6. 以下の情報をメモ:
   ```
   DB_HOST: mysqlXXX.xserver.jp (サーバーパネルに表示されます)
   DB_DATABASE: 作成したデータベース名
   DB_USERNAME: 作成したユーザー名
   DB_PASSWORD: 設定したパスワード
   ```

### 1.2 SSH接続の有効化

1. サーバーパネル → 「SSH設定」
2. 「SSH設定」タブでONに設定
3. 「公開鍵認証用鍵ペアの生成」タブで鍵を生成（推奨）
4. 秘密鍵をダウンロードして保存

### 1.3 PHPバージョンの設定

1. サーバーパネル → 「PHP Ver.切替」
2. `arashiyama-cong-map.net` ドメインを選択
3. PHP 8.3.X を選択
4. 「変更」をクリック

### 1.4 メールアカウントの作成（メール通知機能用）

1. サーバーパネル → 「メールアカウント設定」
2. `arashiyama-cong-map.net` を選択
3. 「メールアカウント追加」タブ
4. `noreply@arashiyama-cong-map.net` を作成
5. パスワードをメモ

### 1.5 SSL証明書の設定（無料）

1. サーバーパネル → 「SSL設定」
2. `arashiyama-cong-map.net` を選択
3. 「独自SSL設定追加」タブ
4. 無料独自SSL（Let's Encrypt）を追加
5. 反映まで数分〜1時間程度待つ

## 2. ローカル環境での準備

### 2.1 .envファイルの設定

`.env.arashiyama` ファイルを編集して、エックスサーバーの情報を入力:

```bash
# データベース情報を入力
DB_HOST=mysqlXXX.xserver.jp
DB_DATABASE=arashiyama_events
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# メール情報を入力
MAIL_USERNAME=noreply@arashiyama-cong-map.net
MAIL_PASSWORD=your-email-password
```

### 2.2 ファイルの準備

デプロイ前に以下を確認:

```bash
# Gitでコミット
git add .
git commit -m "Prepare for production deployment"
git push origin main
```

## 3. サーバーへのデプロイ

### 3.1 SSH接続

```bash
# SSH接続（秘密鍵を使う場合）
ssh -i /path/to/private_key your-account@your-server.xserver.jp

# または、パスワード認証の場合
ssh your-account@your-server.xserver.jp
```

### 3.2 ディレクトリ構成の作成

```bash
# ホームディレクトリに移動
cd ~

# Laravel用のディレクトリを作成
mkdir -p arashiyama-event-app
cd arashiyama-event-app
```

### 3.3 ファイルのアップロード

#### 方法A: Git経由（推奨）

```bash
# GitHubからクローン
git clone https://github.com/your-username/your-repo.git .

# または、ローカルからプッシュしてクローン
```

#### 方法B: SFTP経由

ローカルPCから:
```bash
# srcディレクトリの中身をアップロード
scp -r ./src/* your-account@your-server.xserver.jp:~/arashiyama-event-app/
```

### 3.4 Composerのインストールと依存関係

```bash
cd ~/arashiyama-event-app

# Composerをダウンロード
curl -sS https://getcomposer.org/installer | php

# 本番環境用に依存関係をインストール
php composer.phar install --no-dev --optimize-autoloader
```

### 3.5 環境設定

```bash
# .envファイルを配置
cp .env.arashiyama .env

# または、ローカルから転送した.envをリネーム

# アプリケーションキーを生成
php artisan key:generate

# .envの内容を再確認
nano .env
```

### 3.6 パーミッション設定

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 3.7 データベースのセットアップ

```bash
# マイグレーション実行
php artisan migrate --force

# 初期データ投入（必要に応じて）
php artisan db:seed --force
```

### 3.8 最適化

```bash
# キャッシュ生成
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3.9 公開ディレクトリの設定

```bash
# ドメインのディレクトリに移動
cd ~/arashiyama-cong-map.net/

# 既存のpublic_htmlを削除（念のためバックアップ）
mv public_html public_html.backup

# Laravelのpublicフォルダへのシンボリックリンクを作成
ln -s ~/arashiyama-event-app/public public_html

# ストレージリンクの作成
cd ~/arashiyama-event-app
php artisan storage:link
```

### 3.10 .htaccessの配置

```bash
# エックスサーバー用の.htaccessを使用
cd ~/arashiyama-event-app/public
cp .htaccess.xserver .htaccess

# または、既存の.htaccessに以下を追加
nano .htaccess
# 以下を先頭に追加:
# AddHandler application/x-httpd-php8.3 .php
```

## 4. 動作確認

### 4.1 アクセステスト

ブラウザで以下にアクセス:
- https://arashiyama-cong-map.net

### 4.2 確認項目

- [ ] トップページが表示される
- [ ] HTTPSで接続できる（緑の鍵マーク）
- [ ] 新規登録ができる
- [ ] ログインができる
- [ ] イベント一覧が表示される
- [ ] 管理画面にアクセスできる

### 4.3 エラーが発生した場合

```bash
# エラーログを確認
tail -f ~/arashiyama-event-app/storage/logs/laravel.log

# キャッシュクリア
cd ~/arashiyama-event-app
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 再度キャッシュ生成
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 5. 管理者アカウントの作成

初回デプロイ後、管理者アカウントを作成:

```bash
cd ~/arashiyama-event-app
php artisan tinker
```

Tinkerで以下を実行:

```php
$user = new App\Models\User();
$user->name = '管理者';
$user->email = 'admin@arashiyama-cong-map.net';
$user->password = bcrypt('secure-password-here');
$user->is_admin = true;
$user->save();
```

## 6. メンテナンス・更新方法

### アプリケーションの更新

```bash
ssh your-account@your-server.xserver.jp
cd ~/arashiyama-event-app

# メンテナンスモード開始
php artisan down

# 最新版を取得（Git使用の場合）
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

## 7. バックアップ

### データベースバックアップ

```bash
# mysqldumpでバックアップ
mysqldump -h mysqlXXX.xserver.jp -u your_db_user -p arashiyama_events > ~/backups/db_backup_$(date +%Y%m%d_%H%M%S).sql
```

### 定期バックアップの設定（cron）

```bash
# cron設定を編集
crontab -e

# 以下を追加（毎日午前3時にバックアップ）
0 3 * * * mysqldump -h mysqlXXX.xserver.jp -u your_db_user -pyour_password arashiyama_events > ~/backups/db_backup_$(date +\%Y\%m\%d).sql
```

## トラブルシューティング

### 500 Internal Server Error

```bash
# パーミッション確認
chmod -R 775 ~/arashiyama-event-app/storage
chmod -R 775 ~/arashiyama-event-app/bootstrap/cache

# ログ確認
tail -f ~/arashiyama-event-app/storage/logs/laravel.log
```

### CSS/JSが読み込まれない

- ブラウザのキャッシュをクリア
- シンボリックリンクを確認: `ls -la ~/arashiyama-cong-map.net/public_html`
- .envのAPP_URLを確認: `https://arashiyama-cong-map.net`

### データベース接続エラー

- .envのDB_*設定を再確認
- エックスサーバーのサーバーパネルでホスト名を確認
- データベースユーザーの権限を確認

## 完了！

以上でデプロイは完了です。

問題が発生した場合は、DEPLOY.md の詳細なトラブルシューティングセクションも参照してください。
