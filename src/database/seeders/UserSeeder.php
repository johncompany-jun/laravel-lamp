<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('Gosho0059'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create 40 general users
        $japaneseNames = [
            '田中太郎', '佐藤花子', '鈴木一郎', '高橋美咲', '伊藤健太',
            '渡辺あゆみ', '山本大輔', '中村さくら', '小林誠', '加藤優子',
            '吉田和也', '山田麻衣', '佐々木拓海', '山口結衣', '松本翔太',
            '井上彩香', '木村雄太', '林美優', '斎藤直樹', '清水香織',
            '森田健', '池田莉子', '橋本修', '山崎愛', '石川大樹',
            '前田奈々', '岡田悠斗', '藤田真理', '長谷川航', '村上優奈',
            '近藤龍一', '後藤美穂', '坂本翼', '遠藤凛', '青木俊介',
            '西村香苗', '藤井颯', '福田千尋', '太田陸', '三浦結菜',
        ];

        foreach ($japaneseNames as $index => $name) {
            User::create([
                'name' => $name,
                'email' => 'user' . ($index + 1) . '@example.com',
                'password' => Hash::make('Gosho0059'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
        }
    }
}
