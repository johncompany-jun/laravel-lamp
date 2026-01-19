# pw.goshocong-map.com へのデプロイ手順

このドキュメントは、`pw.goshocong-map.com` サブドメインへのデプロイ専用の手順書です。

## サーバー構造

```
~/goshocong-map.com/
├── pw/                          # Laravel本体（非公開領域）
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   │   └── build/               # Viteビルドファイル（コピー必要）
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   └── .env
└── public_html/
    └── pw.goshocong-map.com/    # 公開フォルダ（サブドメイン）
        ├── build/
        │   ├── assets/
        │   └── manifest.json
        ├── index.php            # パス修正済み
        └── .htaccess
```

## 前提条件

- エックスサーバーのアカウントとサブドメイン設定が完了していること
- ローカル環境にNode.js、npm、SSH鍵が設定されていること
- Docker環境でLaravelアプリケーションが動作していること
- PHP 8.2以上がサーバーで有効になっていること

## 1. エックスサーバー側の準備

### 1.1 サブドメインの作成

1. サーバーパネル → 「サブドメイン設定」
2. `goshocong-map.com` を選択
3. サブドメイン `pw` を追加
4. 公開フォルダは `~/goshocong-map.com/public_html/pw.goshocong-map.com/` に自動作成される

### 1.2 PHPバージョンの設定

1. サーバーパネル → 「PHP Ver.切替」
2. `goshocong-map.com` または `pw.goshocong-map.com` を選択
3. PHP 8.2 以上に変更

### 1.3 データベース設定

既存のデータベースを使用（arashiyamaと同じ）:
```
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=johncompany_arashiyama
DB_USERNAME=johncompany_jwar
DB_PASSWORD=jw1914arashiyama
```

### 1.4 SSH接続の設定

1. サーバーパネル → 「SSH設定」
2. 「SSH設定」タブでONに設定
3. SSH接続テスト:
   ```bash
   ssh -i ~/.ssh/johncompany.key -p 10022 johncompany@sv16408.xserver.jp
   ```

## 2. ローカル環境での準備

### 2.1 .envファイルの確認

`.env.gosho` ファイルが既に設定されていることを確認してください。

### 2.2 フロントエンドアセットのビルド

```bash
cd /Users/matsuijun/src/laravel-lamp/src

# 本番環境用にビルド
rm -f public/hot
APP_ENV=production npm run build
```

ビルドが完了すると、`public/build/` ディレクトリにCSS/JavaScriptファイルが生成されます。

## 3. サーバーへのデプロイ

### 3.1 Laravel本体をアップロード（public除く）

ローカルのターミナル（`/Users/matsuijun/src/laravel-lamp`）で実行:

```bash
rsync -avz \
  --exclude 'node_modules' \
  --exclude '.git' \
  --exclude 'storage/logs/*' \
  --exclude 'tests' \
  --exclude 'public' \
  -e "ssh -i ~/.ssh/johncompany.key -p 10022" \
  ./src/ \
  johncompany@sv16408.xserver.jp:~/goshocong-map.com/pw/
```

### 3.2 publicフォルダを公開ディレクトリにアップロード

```bash
rsync -avz \
  -e "ssh -i ~/.ssh/johncompany.key -p 10022" \
  ./src/public/ \
  johncompany@sv16408.xserver.jp:~/goshocong-map.com/public_html/pw.goshocong-map.com/
```

### 3.3 SSH接続してサーバー設定

```bash
ssh -i ~/.ssh/johncompany.key -p 10022 johncompany@sv16408.xserver.jp
```

### 3.4 index.phpのパス修正

公開フォルダの `index.php` がLaravel本体を参照するようにパスを修正:

```bash
cd ~/goshocong-map.com/public_html/pw.goshocong-map.com
sed -i "s|__DIR__.'/../vendor|__DIR__.'/../../pw/vendor|g" index.php
sed -i "s|__DIR__.'/../bootstrap|__DIR__.'/../../pw/bootstrap|g" index.php
```

### 3.5 環境設定とComposerインストール

```bash
cd ~/goshocong-map.com/pw

# .envファイルを配置
cp .env.gosho .env

# Composer依存関係をインストール
composer install --no-dev --optimize-autoloader

# アプリケーションキーを生成
php artisan key:generate
```

### 3.6 データベースのセットアップ

```bash
php artisan migrate --force
```

### 3.7 パーミッション設定

```bash
chmod -R 775 storage bootstrap/cache
```

### 3.8 Viteビルドファイルをpw/publicにもコピー

**重要**: LaravelがViteマニフェストを参照するため、`pw/public/build/` にもビルドファイルが必要です。

```bash
mkdir -p ~/goshocong-map.com/pw/public/build
cp -r ~/goshocong-map.com/public_html/pw.goshocong-map.com/build/* ~/goshocong-map.com/pw/public/build/
```

### 3.9 キャッシュクリアと最適化

```bash
cd ~/goshocong-map.com/pw
php artisan optimize:clear
php artisan config:cache
php artisan view:clear
```

## 4. 動作確認

### 4.1 アクセステスト

ブラウザで以下にアクセス:
```
https://pw.goshocong-map.com/
```

### 4.2 確認項目

- [ ] トップページが表示される（CSSが適用されている）
- [ ] HTTPSで接続できる
- [ ] ログインページが表示される
- [ ] 管理者でログインできる

## 5. アプリケーション更新時の手順

### 5.1 ローカルでビルド

```bash
cd /Users/matsuijun/src/laravel-lamp/src
rm -f public/hot
APP_ENV=production npm run build
```

### 5.2 Laravel本体をアップロード

```bash
cd /Users/matsuijun/src/laravel-lamp

rsync -avz \
  --exclude 'node_modules' \
  --exclude '.git' \
  --exclude 'storage/logs/*' \
  --exclude 'tests' \
  --exclude 'public' \
  -e "ssh -i ~/.ssh/johncompany.key -p 10022" \
  ./src/ \
  johncompany@sv16408.xserver.jp:~/goshocong-map.com/pw/
```

### 5.3 publicフォルダをアップロード

```bash
rsync -avz \
  -e "ssh -i ~/.ssh/johncompany.key -p 10022" \
  ./src/public/ \
  johncompany@sv16408.xserver.jp:~/goshocong-map.com/public_html/pw.goshocong-map.com/
```

### 5.4 サーバーでキャッシュクリア

```bash
ssh -i ~/.ssh/johncompany.key -p 10022 johncompany@sv16408.xserver.jp
cd ~/goshocong-map.com/pw

# Viteビルドファイルをコピー
cp -r ~/goshocong-map.com/public_html/pw.goshocong-map.com/build/* ~/goshocong-map.com/pw/public/build/

# キャッシュクリア
php artisan optimize:clear
php artisan config:cache
php artisan view:clear

# マイグレーションがあれば実行
php artisan migrate --force
```

## 6. トラブルシューティング

### Vite manifest not found エラー

**症状**: `Vite manifest not found at: .../pw/public/build/manifest.json`

**原因**: `pw/public/build/` にビルドファイルがない

**解決方法**:
```bash
mkdir -p ~/goshocong-map.com/pw/public/build
cp -r ~/goshocong-map.com/public_html/pw.goshocong-map.com/build/* ~/goshocong-map.com/pw/public/build/
```

### PHPバージョンエラー

**症状**: `Your Composer dependencies require a PHP version ">= 8.2.0"`

**解決方法**: サーバーパネルでPHP 8.2以上に切り替え

### 500 Internal Server Error

```bash
chmod -R 775 storage bootstrap/cache
tail -f storage/logs/laravel.log
```

## 7. 重要なポイントまとめ

1. **サブドメインの構造を理解する**
   - Laravel本体: `~/goshocong-map.com/pw/`
   - 公開フォルダ: `~/goshocong-map.com/public_html/pw.goshocong-map.com/`

2. **index.phpのパス修正が必要**
   - `__DIR__.'/../vendor'` → `__DIR__.'/../../pw/vendor'`
   - `__DIR__.'/../bootstrap'` → `__DIR__.'/../../pw/bootstrap'`

3. **Viteビルドファイルは2箇所に必要**
   - `public_html/pw.goshocong-map.com/build/`（ブラウザからのアクセス用）
   - `pw/public/build/`（Laravelのマニフェスト参照用）

4. **本番ビルドは必ず `APP_ENV=production` で実行**

5. **変更後はキャッシュクリア（`php artisan optimize:clear`）**

## 完了

以上でデプロイ手順は完了です。
