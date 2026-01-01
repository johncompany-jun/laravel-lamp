<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventApplication;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventApplicationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 対象イベント（ID: 2, 4, 5）
        $eventIds = [2, 4, 5];

        // 一般ユーザー30人（user1@example.com 〜 user30@example.com）
        $users = User::whereBetween('id', [3, 32])->get();

        $applications = [];
        $totalCount = 0;

        foreach ($eventIds as $eventId) {
            $event = Event::with('applicationSlots')->find($eventId);

            if (!$event || $event->applicationSlots->count() === 0) {
                $this->command->warn("イベントID {$eventId} はスキップされました（スロットなし）");
                continue;
            }

            foreach ($users as $user) {
                // 各ユーザーは全てのスロットに申し込む
                foreach ($event->applicationSlots as $slot) {
                    $applications[] = [
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'event_application_slot_id' => $slot->id,
                        'availability' => 'available', // 参加可能
                        'can_help_setup' => (bool)rand(0, 1), // ランダムでsetupヘルプ
                        'can_help_cleanup' => (bool)rand(0, 1), // ランダムでcleanupヘルプ
                        'comment' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $totalCount++;
                }
            }

            $this->command->info("イベントID {$eventId}: {$users->count()}人 × {$event->applicationSlots->count()}スロット = " . ($users->count() * $event->applicationSlots->count()) . "件の申込");
        }

        // 一括挿入
        foreach (array_chunk($applications, 500) as $chunk) {
            EventApplication::insert($chunk);
        }

        $this->command->info("合計 {$totalCount} 件の申込を作成しました。");
    }
}
