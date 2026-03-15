<?php

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\Event\ValueObjects\EventPeriod;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EventPeriodTest extends TestCase
{
    // --- 生成 ---

    public function test_正常な時間帯で生成できる(): void
    {
        $period = new EventPeriod('09:00', '17:00');

        $this->assertSame('09:00:00', $period->startTimeString());
        $this->assertSame('17:00:00', $period->endTimeString());
    }

    public function test_終了が開始と同じ場合は例外(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new EventPeriod('09:00', '09:00');
    }

    public function test_終了が開始より前の場合は例外(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new EventPeriod('17:00', '09:00');
    }

    // --- durationMinutes ---

    public function test_合計時間を分で返す(): void
    {
        $period = new EventPeriod('09:00', '11:30');

        $this->assertSame(150, $period->durationMinutes());
    }

    // --- splitIntoSlots ---

    public function test_スロットに正しく分割できる(): void
    {
        $period = new EventPeriod('09:00', '11:00');
        $slots  = $period->splitIntoSlots(60);

        $this->assertCount(2, $slots);
        $this->assertSame('09:00:00', $slots[0]['start']);
        $this->assertSame('10:00:00', $slots[0]['end']);
        $this->assertSame('10:00:00', $slots[1]['start']);
        $this->assertSame('11:00:00', $slots[1]['end']);
    }

    public function test_割り切れない場合は端数スロットを除外する(): void
    {
        $period = new EventPeriod('09:00', '10:30');
        $slots  = $period->splitIntoSlots(60);

        // 09:00-10:00 のみ（10:00-11:00 は 10:30 を超えるので除外）
        $this->assertCount(1, $slots);
    }

    public function test_スロット時間が0以下の場合は例外(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $period = new EventPeriod('09:00', '17:00');
        $period->splitIntoSlots(0);
    }

    // --- isDifferentFrom ---

    public function test_同じ時間は差分なし(): void
    {
        $period = new EventPeriod('09:00', '17:00');

        $this->assertFalse($period->isDifferentFrom('09:00', '17:00'));
    }

    public function test_開始時刻が変わったら差分あり(): void
    {
        $period = new EventPeriod('09:00', '17:00');

        $this->assertTrue($period->isDifferentFrom('10:00', '17:00'));
    }

    public function test_終了時刻が変わったら差分あり(): void
    {
        $period = new EventPeriod('09:00', '17:00');

        $this->assertTrue($period->isDifferentFrom('09:00', '18:00'));
    }
}
