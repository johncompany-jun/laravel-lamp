# arashiyama-cong-map.net へのデプロイ手順

このドキュメントは、`arashiyama-cong-map.net` ドメインへのデプロイ専用の手順書です。

## 前提条件

- エックスサーバーのアカウントとドメイン設定が完了していること
- ローカル環境にNode.js、npm、SSH鍵が設定されていること
- Docker環境でLaravelアプリケーションが動作していること

## 1. エックスサーバー側の準備

### 1.1 データベース設定

既に作成されている場合はこのステップはスキップしてください。

データベース情報:
```
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=johncompany_arashiyama
DB_USERNAME=johncompany_jwar
DB_PASSWORD=jw1914arashiyama
```

### 1.2 SSH接続の設定

1. サーバーパネル → 「SSH設定」
2. 「SSH設定」タブでONに設定
3. 「公開鍵認証用鍵ペアの生成」タブで鍵ペアを生成
4. 秘密鍵ファイル（例: `sv16408.key`）をダウンロード
5. ローカルに保存して権限を設定:
   ```bash
   mv ~/Downloads/sv*.key ~/.ssh/xserver.key
   chmod 600 ~/.ssh/xserver.key
   ```

6. SSH接続テスト:
   ```bash
   ssh -i ~/.ssh/xserver.key -p 10022 johncompany@sv16408.xserver.jp
   ```

   パスフレーズはサーバーID（`johncompany`）と同じです。

## 2. ローカル環境での準備

### 2.1 .envファイルの確認

`.env.arashiyama` ファイルが既に設定されていることを確認してください。

### 2.2 フロントエンドアセットのビルド

**重要**: 本番環境ではViteの開発サーバーは動作しません。`APP_ENV=production` で事前にアセットをビルドする必要があります。

```bash
cd /path/to/laravel-lamp/src

# npm依存関係を再インストール（node_modulesに問題がある場合）
rm -rf node_modules package-lock.json
npm install

# 本番環境用にビルド（重要: APP_ENV=production を指定）
APP_ENV=production npm run build
```

ビルドが完了すると、`public/build/` ディレクトリにCSS/JavaScriptファイルが生成されます。

**重要な注意点**:
- `public/hot` ファイルが存在すると、開発モードと判断されてVite開発サーバーに接続しようとします
- ローカルで `npm run dev` が起動している場合は停止してください
- ビルド後、`public/hot` ファイルが存在しないことを確認してください

## 3. サーバーへのデプロイ

### 3.1 rsyncでファイルをアップロード

ローカル環境から、ログアウトした状態で実行します。

```bash
# 初回デプロイまたは全ファイル更新
rsync -avz \
  --exclude 'node_modules' \
  --exclude '.git' \
  --exclude 'storage/logs/*' \
  --exclude 'tests' \
  -e "ssh -i ~/.ssh/xserver.key -p 10022" \
  /Users/yourname/src/laravel-lamp/src/ \
  johncompany@sv16408.xserver.jp:~/arashiyama-cong-map.net/
```

パスフレーズ（`johncompany`）を入力してアップロードが完了するまで待ちます。

### 3.2 SSH接続してサーバー設定

```bash
ssh -i ~/.ssh/xserver.key -p 10022 johncompany@sv16408.xserver.jp
cd ~/arashiyama-cong-map.net
```

### 3.3 環境設定とComposerインストール

```bash
# .envファイルを配置
cp .env.arashiyama .env

# Composer依存関係をインストール
composer install --no-dev --optimize-autoloader

# アプリケーションキーを生成
php artisan key:generate
```

### 3.4 データベースのセットアップ

```bash
# マイグレーション実行
php artisan migrate --force
```

### 3.5 パーミッション設定

```bash
chmod -R 775 storage bootstrap/cache
```

### 3.6 public_htmlの設定

```bash
# publicディレクトリの内容をpublic_htmlにコピー
cp -r public/* public_html/

# Laravelの.htaccessをコピー（エックスサーバーのキャッシュ設定も追加）
cp public/.htaccess public_html/.htaccess

# エックスサーバーのキャッシュ設定を追加
cat >> public_html/.htaccess << 'EOF'

# X-Server Cache Settings
SetEnvIf Request_URI ".*" Ngx_Cache_NoCacheMode=off
SetEnvIf Request_URI ".*" Ngx_Cache_AllCacheMode
EOF
```

### 3.7 キャッシュクリアと最適化

```bash
# すべてのキャッシュをクリア
php artisan optimize:clear

# 設定をキャッシュ
php artisan config:cache

# ビューキャッシュをクリア
php artisan view:clear
```

### 3.8 重要: public/hotファイルの削除

```bash
# 開発モード判定ファイルを削除（重要！）
rm -f public/hot

# 確認
ls -la public/hot  # "そのようなファイルやディレクトリはありません" と表示されればOK
```

**このファイルが残っていると、ViteがローカルホストのURL（localhost:5173）を参照しようとしてCSSが読み込まれません。**

## 4. 動作確認

### 4.1 アクセステスト

ブラウザで以下にアクセス:
```
https://arashiyama-cong-map.net/
```

ログインページまたはダッシュボードが正常に表示され、CSSが適用されていることを確認してください。

### 4.2 CSSが適用されない場合のトラブルシューティング

ブラウザの開発者ツール（F12）のコンソールで以下のエラーが出ている場合：

```
Failed to load resource: net::ERR_CONNECTION_REFUSED
http://localhost:5173/@vite/client
```

これは `public/hot` ファイルが残っているか、キャッシュが古い状態です。

**解決方法:**

```bash
# サーバーでpublic/hotを削除
rm -f public/hot

# キャッシュクリア
php artisan optimize:clear
php artisan config:cache
php artisan view:clear

# 確認
curl -s https://arashiyama-cong-map.net/ | grep -E "vite|script|link.*css" | head -5
```

`localhost:5173` への参照がなく、`/build/assets/app-*.css` のような本番用のパスになっていればOKです。

ブラウザで強制リロード（Cmd+Shift+R）してください。

### 4.3 確認項目

- [ ] トップページが表示される（CSSが適用されている）
- [ ] HTTPSで接続できる
- [ ] ログインページが表示される
- [ ] 管理者でログインできる

## 5. 管理者アカウントの作成

初回デプロイ後、管理者アカウントを作成します。

```bash
cd ~/arashiyama-cong-map.net
php artisan tinker
```

Tinkerで以下を1行ずつ実行:

```php
$user = new App\Models\User();
$user->name = 'Admin User';
$user->email = 'admin@example.com';
$user->password = bcrypt('password');
$user->role = 'admin';
$user->save();
exit
```

ログイン情報:
- Email: `admin@example.com`
- Password: `password`

**セキュリティ**: 本番運用前に強力なパスワードに変更してください。

## 6. アプリケーション更新時の手順

コードを変更した後、サーバーに反映する手順です。

### 6.1 ローカルでビルド

```bash
cd /path/to/laravel-lamp/src

# 本番用にビルド
APP_ENV=production npm run build

# public/hotが存在しないことを確認
ls public/hot  # エラーが出ればOK
```

### 6.2 変更ファイルのみアップロード

全ファイルではなく、ビルドファイルのみアップロードする場合：

```bash
# ビルドファイルのみアップロード
rsync -avz \
  -e "ssh -i ~/.ssh/xserver.key -p 10022" \
  /Users/yourname/src/laravel-lamp/src/public/build/ \
  johncompany@sv16408.xserver.jp:~/arashiyama-cong-map.net/public_html/build/
```

PHPコードやBladeファイルも変更した場合は、セクション3.1の全体アップロードを実行してください。

### 6.3 サーバーでキャッシュクリア

```bash
ssh -i ~/.ssh/xserver.key -p 10022 johncompany@sv16408.xserver.jp
cd ~/arashiyama-cong-map.net

# public/hotを削除
rm -f public/hot

# キャッシュクリア
php artisan optimize:clear
php artisan config:cache
php artisan view:clear

# マイグレーションがあれば実行
php artisan migrate --force
```

## 7. トラブルシューティング

### CSS/JSが読み込まれない（ERR_CONNECTION_REFUSED）

**症状**: ブラウザコンソールで `http://localhost:5173/@vite/client` へのエラー

**原因**: `public/hot` ファイルが残っている

**解決方法**:
```bash
# サーバー側
rm -f public/hot
php artisan optimize:clear

# ローカル側（再ビルドする場合）
rm -f public/hot
APP_ENV=production npm run build
```

### 500 Internal Server Error

```bash
# パーミッション確認
chmod -R 775 storage bootstrap/cache

# エラーログ確認
tail -f storage/logs/laravel.log
```

### データベース接続エラー

```bash
# .env設定を確認
cat .env | grep DB_

# 正しい設定:
# DB_HOST=localhost（エックスサーバーの場合）
# DB_DATABASE=johncompany_arashiyama
# DB_USERNAME=johncompany_jwar
# DB_PASSWORD=jw1914arashiyama
```

## 8. 重要なポイントまとめ

1. **本番ビルドは必ず `APP_ENV=production` で実行**
2. **`public/hot` ファイルは絶対にサーバーにアップロードしない**
3. **デプロイ後は必ず `rm -f public/hot` を実行**
4. **変更後はキャッシュクリア（`php artisan optimize:clear`）**
5. **ブラウザの強制リロード（Cmd+Shift+R）で確認**

## 完了

以上でデプロイ手順は完了です。
