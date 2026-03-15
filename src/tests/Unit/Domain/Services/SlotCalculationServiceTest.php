<?php

namespace Tests\Unit\Domain\Services;

use App\Domain\Event\Services\SlotCalculationService;
use App\Domain\Event\ValueObjects\EventPeriod;
use PHPUnit\Framework\TestCase;

class SlotCalculationServiceTest extends TestCase
{
    private SlotCalculationService $service;

    protected function setUp(): void
    {
        $this->service = new SlotCalculationService();
    }

    // --- calculateApplicationSlots ---

    public function test_申込スロットを正しく計算できる(): void
    {
        $period = new EventPeriod('09:00', '11:00');
        $slots  = $this->service->calculateApplicationSlots($period, 60);

        $this->assertCount(2, $slots);
        $this->assertSame('09:00:00', $slots[0]['start']);
        $this->assertSame('10:00:00', $slots[0]['end']);
    }

    // --- calculateAssignmentSlots ---

    public function test_場所なしでアサインメントスロットを計算できる(): void
    {
        $period = new EventPeriod('09:00', '11:00');
        $slots  = $this->service->calculateAssignmentSlots($period, 60);

        $this->assertCount(2, $slots);
        $this->assertNull($slots[0]['location']);
        $this->assertSame(3, $slots[0]['capacity']);
    }

    public function test_複数場所でアサインメントスロットを展開できる(): void
    {
        $period    = new EventPeriod('09:00', '11:00');
        $locations = ['A会場', 'B会場'];
        $slots     = $this->service->calculateAssignmentSlots($period, 60, $locations);

        // 2時間 × 2場所 = 4スロット
        $this->assertCount(4, $slots);
        $this->assertSame('A会場', $slots[0]['location']);
        $this->assertSame('B会場', $slots[1]['location']);
        $this->assertSame('A会場', $slots[2]['location']);
        $this->assertSame('B会場', $slots[3]['location']);
    }

    public function test_カスタムキャパシティを指定できる(): void
    {
        $period = new EventPeriod('09:00', '10:00');
        $slots  = $this->service->calculateAssignmentSlots($period, 60, [], 5);

        $this->assertSame(5, $slots[0]['capacity']);
    }

    // --- needsApplicationSlotRegeneration ---

    public function test_変更なしは再生成不要(): void
    {
        $period = new EventPeriod('09:00', '17:00');

        $result = $this->service->needsApplicationSlotRegeneration(
            $period, '09:00', '17:00', 60, 60
        );

        $this->assertFalse($result);
    }

    public function test_開始時刻変更で再生成必要(): void
    {
        $period = new EventPeriod('09:00', '17:00');

        $result = $this->service->needsApplicationSlotRegeneration(
            $period, '10:00', '17:00', 60, 60
        );

        $this->assertTrue($result);
    }

    public function test_スロット時間変更で再生成必要(): void
    {
        $period = new EventPeriod('09:00', '17:00');

        $result = $this->service->needsApplicationSlotRegeneration(
            $period, '09:00', '17:00', 60, 90
        );

        $this->assertTrue($result);
    }

    // --- needsAssignmentSlotRegeneration ---

    public function test_場所変更でアサインメントスロット再生成必要(): void
    {
        $period = new EventPeriod('09:00', '17:00');

        $result = $this->service->needsAssignmentSlotRegeneration(
            $period, '09:00', '17:00', 60, 60,
            ['A会場'],
            ['A会場', 'B会場'],
        );

        $this->assertTrue($result);
    }

    public function test_変更なしはアサインメントスロット再生成不要(): void
    {
        $period = new EventPeriod('09:00', '17:00');

        $result = $this->service->needsAssignmentSlotRegeneration(
            $period, '09:00', '17:00', 60, 60,
            ['A会場'],
            ['A会場'],
        );

        $this->assertFalse($result);
    }
}
