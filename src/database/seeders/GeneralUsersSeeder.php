<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GeneralUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 日本人の名前リスト
        $firstNames = [
            '太郎', '次郎', '三郎', '花子', '美咲', '健太', '翔太', '陽菜', '結衣', '葵',
            '大輔', '拓也', '裕太', '麻衣', '彩香', '直樹', '優子', '真理', '智子', '恵子',
            '隆', '修', '誠', '薫', '明', '聡', '豊', '茜', 'さくら', '舞',
        ];

        $lastNames = [
            '佐藤', '鈴木', '高橋', '田中', '伊藤', '渡辺', '山本', '中村', '小林', '加藤',
        ];

        $users = [];
        $count = 0;

        foreach ($lastNames as $lastName) {
            foreach ($firstNames as $firstName) {
                if ($count >= 30) break 2;

                $name = $lastName . ' ' . $firstName;
                $email = 'user' . ($count + 1) . '@example.com';

                $users[] = [
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $count++;
            }
        }

        User::insert($users);

        $this->command->info('30人の一般ユーザーを作成しました。');
        $this->command->info('メールアドレス: user1@example.com 〜 user30@example.com');
        $this->command->info('パスワード: password');
    }
}
