<?php

namespace Database\Seeders;

use App\Enums\ApplicationSlotDuration;
use App\Enums\AssignmentSlotDuration;
use App\Enums\EventStatus;
use App\Models\User;
use App\UseCases\CreateEventUseCase;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function __construct(
        private CreateEventUseCase $createEventUseCase
    ) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $this->command->error('Admin user not found. Please run UserSeeder first.');
            return;
        }

        // パターン1: シンプルなイベント（単一ロケーション）
        $this->createEventUseCase->execute([
            'title' => '三条京阪PW',
            'event_date' => now()->addDays(3)->format('Y-m-d'),
            'start_time' => '13:00',
            'end_time' => '16:00',
            'status' => EventStatus::OPEN->value,
            'application_slot_duration' => ApplicationSlotDuration::ONE_HOUR->value,
            'slot_duration' => AssignmentSlotDuration::TWENTY_MINUTES->value,
            'created_by' => $admin->id,
            'location' => '三条京阪駅周辺',
            'locations' => ['北側エリア', '南側エリア'],
            'notes' => 'シンプルなイベントのサンプルです。2つのアサインエリアがあります。',
        ]);

        // パターン2: 複数ロケーション
        $this->createEventUseCase->execute([
            'title' => '京都駅周辺PW',
            'event_date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '14:00',
            'end_time' => '17:00',
            'status' => EventStatus::OPEN->value,
            'application_slot_duration' => ApplicationSlotDuration::ONE_HOUR->value,
            'slot_duration' => AssignmentSlotDuration::TWENTY_MINUTES->value,
            'created_by' => $admin->id,
            'locations' => ['北西エリア', '北東エリア', '南側エリア'],
            'notes' => '複数ロケーションのサンプルです。',
        ]);

        // パターン3: 15分スロット
        $this->createEventUseCase->execute([
            'title' => '河原町PW（短時間）',
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => EventStatus::OPEN->value,
            'application_slot_duration' => ApplicationSlotDuration::THIRTY_MINUTES->value,
            'slot_duration' => AssignmentSlotDuration::FIFTEEN_MINUTES->value,
            'created_by' => $admin->id,
            'location' => '河原町駅周辺',
            'locations' => ['東側エリア', '西側エリア', '中央エリア'],
            'notes' => '15分スロットのサンプルです。3つのアサインエリアがあります。',
        ]);

        // パターン4: 繰り返しイベント（週次）
        $this->createEventUseCase->execute([
            'title' => '定期PW（毎週）',
            'event_date' => now()->addDays(14)->format('Y-m-d'),
            'start_time' => '13:00',
            'end_time' => '15:00',
            'status' => EventStatus::DRAFT->value,
            'application_slot_duration' => ApplicationSlotDuration::THIRTY_MINUTES->value,
            'slot_duration' => AssignmentSlotDuration::TWENTY_MINUTES->value,
            'created_by' => $admin->id,
            'location' => '梅田駅周辺',
            'locations' => ['A地点', 'B地点'],
            'is_recurring' => true,
            'recurrence_type' => 'weekly',
            'recurrence_end_date' => now()->addDays(14 + 28)->format('Y-m-d'), // 4週間
            'notes' => '繰り返しイベントのサンプルです（4週間）。2つのアサインエリアがあります。',
        ]);

        // パターン5: 複数ロケーション + 10分スロット
        $this->createEventUseCase->execute([
            'title' => '大阪城PW（詳細エリア）',
            'event_date' => now()->addDays(21)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'status' => EventStatus::DRAFT->value,
            'application_slot_duration' => ApplicationSlotDuration::ONE_HOUR->value,
            'slot_duration' => AssignmentSlotDuration::TEN_MINUTES->value,
            'created_by' => $admin->id,
            'locations' => ['天守閣エリア', '西の丸庭園', '梅林エリア', '桜門エリア'],
            'notes' => '10分スロット + 4ロケーションのサンプルです。',
        ]);

        // パターン6: テンプレート用イベント
        $this->createEventUseCase->execute([
            'title' => 'PWテンプレート（標準）',
            'event_date' => now()->addDays(1)->format('Y-m-d'), // ダミー日付
            'start_time' => '13:00',
            'end_time' => '16:00',
            'status' => EventStatus::DRAFT->value,
            'application_slot_duration' => ApplicationSlotDuration::ONE_HOUR->value,
            'slot_duration' => AssignmentSlotDuration::TWENTY_MINUTES->value,
            'created_by' => $admin->id,
            'locations' => ['エリア1', 'エリア2'],
            'is_template' => true,
            'notes' => 'イベント作成時に使用できるテンプレートです。2つのアサインエリアがあります。',
        ]);

        // パターン7: 長時間イベント（30分スロット）
        $this->createEventUseCase->execute([
            'title' => '奈良公園PW（長時間）',
            'event_date' => now()->addDays(28)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '16:00',
            'status' => EventStatus::DRAFT->value,
            'application_slot_duration' => ApplicationSlotDuration::TWO_HOURS->value,
            'slot_duration' => AssignmentSlotDuration::THIRTY_MINUTES->value,
            'created_by' => $admin->id,
            'locations' => ['東大寺エリア', '春日大社エリア'],
            'notes' => '6時間の長時間イベントです。30分スロットを使用。',
        ]);

        $this->command->info('イベントデータを作成しました。');
    }
}
