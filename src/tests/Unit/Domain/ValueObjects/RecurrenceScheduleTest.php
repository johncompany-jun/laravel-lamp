<?php

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\Event\ValueObjects\RecurrenceSchedule;
use Carbon\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RecurrenceScheduleTest extends TestCase
{
    // --- ファクトリメソッド ---

    public function test_none_で繰り返しなしを生成できる(): void
    {
        $schedule = RecurrenceSchedule::none();

        $this->assertFalse($schedule->isRecurring());
        $this->assertFalse($schedule->isWeekly());
        $this->assertNull($schedule->recurrenceType());
        $this->assertNull($schedule->recurrenceEndDate());
    }

    public function test_weekly_で毎週繰り返しを生成できる(): void
    {
        $schedule = RecurrenceSchedule::weekly('2025-12-31');

        $this->assertTrue($schedule->isRecurring());
        $this->assertTrue($schedule->isWeekly());
        $this->assertSame(RecurrenceSchedule::TYPE_WEEKLY, $schedule->recurrenceType());
        $this->assertNotNull($schedule->recurrenceEndDate());
    }

    public function test_fromArray_で繰り返しなしを生成できる(): void
    {
        $schedule = RecurrenceSchedule::fromArray(['is_recurring' => false]);

        $this->assertFalse($schedule->isRecurring());
    }

    public function test_fromArray_で毎週繰り返しを生成できる(): void
    {
        $schedule = RecurrenceSchedule::fromArray([
            'is_recurring'        => true,
            'recurrence_type'     => 'weekly',
            'recurrence_end_date' => '2025-12-31',
        ]);

        $this->assertTrue($schedule->isWeekly());
    }

    public function test_fromArray_で繰り返しあり_typeなしは例外(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RecurrenceSchedule::fromArray([
            'is_recurring'        => true,
            'recurrence_end_date' => '2025-12-31',
        ]);
    }

    public function test_fromArray_で繰り返しあり_endDateなしは例外(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RecurrenceSchedule::fromArray([
            'is_recurring'    => true,
            'recurrence_type' => 'weekly',
        ]);
    }

    public function test_サポートされていないtypeは例外(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RecurrenceSchedule::fromArray([
            'is_recurring'        => true,
            'recurrence_type'     => 'monthly',
            'recurrence_end_date' => '2025-12-31',
        ]);
    }

    // --- generateWeeklyDates ---

    public function test_毎週の日付リストを正しく生成できる(): void
    {
        $schedule = RecurrenceSchedule::weekly('2025-01-22');
        $baseDate = Carbon::parse('2025-01-01');

        $dates = $schedule->generateWeeklyDates($baseDate);

        $this->assertCount(3, $dates);
        $this->assertSame('2025-01-08', $dates[0]->toDateString());
        $this->assertSame('2025-01-15', $dates[1]->toDateString());
        $this->assertSame('2025-01-22', $dates[2]->toDateString());
    }

    public function test_繰り返しなしの場合は空配列を返す(): void
    {
        $schedule = RecurrenceSchedule::none();
        $dates    = $schedule->generateWeeklyDates(Carbon::parse('2025-01-01'));

        $this->assertEmpty($dates);
    }

    public function test_終了日が翌週より前なら空配列を返す(): void
    {
        $schedule = RecurrenceSchedule::weekly('2025-01-05');
        $baseDate = Carbon::parse('2025-01-01');

        $dates = $schedule->generateWeeklyDates($baseDate);

        $this->assertEmpty($dates);
    }
}
