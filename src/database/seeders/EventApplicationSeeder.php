<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventApplicationSlot;
use App\Models\User;
use App\UseCases\SubmitEventApplicationUseCase;
use Illuminate\Database\Seeder;

class EventApplicationSeeder extends Seeder
{
    public function __construct(
        private SubmitEventApplicationUseCase $submitApplicationUseCase
    ) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 一般ユーザーを取得
        $users = User::where('role', 'user')->get();

        if ($users->count() === 0) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        // OPENステータスのイベントのみ対象
        $openEvents = Event::where('status', 'open')
            ->whereNull('parent_event_id')
            ->with('applicationSlots')
            ->get();

        if ($openEvents->count() === 0) {
            $this->command->info('No open events found. Skipping application seeding.');
            return;
        }

        foreach ($openEvents as $event) {
            $this->createApplicationsForEvent($event, $users);
        }

        $this->command->info('イベント申込データを作成しました。');
    }

    /**
     * 各イベントに対して申込を作成
     */
    private function createApplicationsForEvent(Event $event, $users): void
    {
        $applicationSlots = $event->applicationSlots->sortBy('start_time')->values();

        if ($applicationSlots->count() === 0) {
            return;
        }

        $shuffledUsers = $users->shuffle();
        $index = 0;

        // パターン1: 全スロット参加可能なユーザー（10%、最大4人）
        $count1 = min(4, $shuffledUsers->count());
        for ($i = 0; $i < $count1; $i++) {
            if ($index >= $shuffledUsers->count()) break;
            $this->createFullAvailabilityApplication($shuffledUsers[$index], $event, $applicationSlots);
            $index++;
        }

        // パターン2: 一部スロットのみ参加可能なユーザー（30%、最大12人）
        $count2 = min(12, $shuffledUsers->count() - $index);
        for ($i = 0; $i < $count2; $i++) {
            if ($index >= $shuffledUsers->count()) break;
            $this->createPartialAvailabilityApplication($shuffledUsers[$index], $event, $applicationSlots);
            $index++;
        }

        // パターン3: 前半のみ参加可能なユーザー（15%、最大6人）
        $count3 = min(6, $shuffledUsers->count() - $index);
        for ($i = 0; $i < $count3; $i++) {
            if ($index >= $shuffledUsers->count()) break;
            $this->createFirstHalfApplication($shuffledUsers[$index], $event, $applicationSlots);
            $index++;
        }

        // パターン4: 後半のみ参加可能なユーザー（15%、最大6人）
        $count4 = min(6, $shuffledUsers->count() - $index);
        for ($i = 0; $i < $count4; $i++) {
            if ($index >= $shuffledUsers->count()) break;
            $this->createSecondHalfApplication($shuffledUsers[$index], $event, $applicationSlots);
            $index++;
        }

        // パターン5: ランダムなスロットのみ参加可能なユーザー（残り、最大8人）
        $count5 = min(8, $shuffledUsers->count() - $index);
        for ($i = 0; $i < $count5; $i++) {
            if ($index >= $shuffledUsers->count()) break;
            $this->createRandomAvailabilityApplication($shuffledUsers[$index], $event, $applicationSlots);
            $index++;
        }

        $this->command->info("  - {$event->title}: {$index}人の申込を作成");
    }

    /**
     * 全スロット参加可能な申込を作成
     */
    private function createFullAvailabilityApplication(User $user, Event $event, $applicationSlots): void
    {
        $slots = [];
        foreach ($applicationSlots as $index => $slot) {
            $slots[$index] = [
                'slot_id' => $slot->id,
                'availability' => 'available',
            ];
        }

        try {
            $this->submitApplicationUseCase->execute(
                $user,
                $event,
                $slots,
                rand(0, 1) === 1,
                rand(0, 1) === 1,
                $this->generateComment()
            );
        } catch (\Exception $e) {
            $this->command->error("    エラー ({$user->name}): " . $e->getMessage());
        }
    }

    /**
     * 一部スロットのみ参加可能な申込を作成
     */
    private function createPartialAvailabilityApplication(User $user, Event $event, $applicationSlots): void
    {
        $slots = [];
        $availableCount = rand(ceil($applicationSlots->count() * 0.3), ceil($applicationSlots->count() * 0.7));
        $availableSlots = $applicationSlots->random(min($availableCount, $applicationSlots->count()));

        foreach ($applicationSlots as $index => $slot) {
            $availability = $availableSlots->contains($slot) ? 'available' : (rand(0, 1) === 1 ? 'unavailable' : null);
            if ($availability !== null) {
                $slots[$index] = [
                    'slot_id' => $slot->id,
                    'availability' => $availability,
                ];
            }
        }

        try {
            $this->submitApplicationUseCase->execute(
                $user,
                $event,
                $slots,
                rand(0, 2) === 1,
                rand(0, 2) === 1,
                $this->generateComment()
            );
        } catch (\Exception $e) {
            $this->command->error("    エラー ({$user->name}): " . $e->getMessage());
        }
    }

    /**
     * 前半のみ参加可能な申込を作成
     */
    private function createFirstHalfApplication(User $user, Event $event, $applicationSlots): void
    {
        $slots = [];
        $halfCount = ceil($applicationSlots->count() / 2);

        foreach ($applicationSlots as $index => $slot) {
            $slots[$index] = [
                'slot_id' => $slot->id,
                'availability' => $index < $halfCount ? 'available' : 'unavailable',
            ];
        }

        try {
            $this->submitApplicationUseCase->execute(
                $user,
                $event,
                $slots,
                rand(0, 1) === 1,
                false,
                $this->generateComment()
            );
        } catch (\Exception $e) {
            $this->command->error("    エラー ({$user->name}): " . $e->getMessage());
        }
    }

    /**
     * 後半のみ参加可能な申込を作成
     */
    private function createSecondHalfApplication(User $user, Event $event, $applicationSlots): void
    {
        $slots = [];
        $halfCount = ceil($applicationSlots->count() / 2);

        foreach ($applicationSlots as $index => $slot) {
            $slots[$index] = [
                'slot_id' => $slot->id,
                'availability' => $index >= $halfCount ? 'available' : 'unavailable',
            ];
        }

        try {
            $this->submitApplicationUseCase->execute(
                $user,
                $event,
                $slots,
                false,
                rand(0, 1) === 1,
                $this->generateComment()
            );
        } catch (\Exception $e) {
            $this->command->error("    エラー ({$user->name}): " . $e->getMessage());
        }
    }

    /**
     * ランダムなスロットのみ参加可能な申込を作成
     */
    private function createRandomAvailabilityApplication(User $user, Event $event, $applicationSlots): void
    {
        $slots = [];
        $hasAvailable = false;

        foreach ($applicationSlots as $index => $slot) {
            $rand = rand(0, 2);
            $availability = match($rand) {
                0 => 'available',
                1 => 'unavailable',
                2 => null,
            };

            if ($availability === 'available') {
                $hasAvailable = true;
            }

            if ($availability !== null) {
                $slots[$index] = [
                    'slot_id' => $slot->id,
                    'availability' => $availability,
                ];
            }
        }

        // 少なくとも1つのスロットがavailableであることを保証
        if (!$hasAvailable && $applicationSlots->count() > 0) {
            $randomSlot = $applicationSlots->random();
            $slots[0] = [
                'slot_id' => $randomSlot->id,
                'availability' => 'available',
            ];
        }

        try {
            $this->submitApplicationUseCase->execute(
                $user,
                $event,
                $slots,
                rand(0, 3) === 1,
                rand(0, 3) === 1,
                $this->generateComment()
            );
        } catch (\Exception $e) {
            $this->command->error("    エラー ({$user->name}): " . $e->getMessage());
        }
    }

    /**
     * ランダムにコメントを生成
     */
    private function generateComment(): ?string
    {
        $comments = [
            '参加させていただきます。よろしくお願いいたします。',
            '初めて参加します。楽しみにしています！',
            'できる限り参加させていただきます。',
            null, // コメントなし
            null,
            null,
            '当日はよろしくお願いします。',
            '可能な時間で参加します。',
        ];

        return $comments[array_rand($comments)];
    }
}
