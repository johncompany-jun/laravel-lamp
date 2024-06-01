# laravel-lamp
Laravel(PHP)+Apache+MySQL+phpMyAdminで
コンテナを作成します。

初回
docker-compose up -d --build
次回からは
docker-compose up -d

Laravelのインストール
docker-compose exec php bash

composer create-project laravel/laravel .

.envでデータベース情報を入力し
docker-compose exec php bash
上記の環境下で
php artisan migrate

プロジェクト本体
http://localhost:8080/

phpMyadmin
http://localhost:8081/