<?php

namespace App\Domain\Event\ValueObjects;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * イベントの時間帯を表すValueObject
 *
 * start_time と end_time を一体として管理し、
 * 「終了は開始より後」という不変条件を保証する
 */
final class EventPeriod
{
    private readonly Carbon $start;
    private readonly Carbon $end;

    public function __construct(string $startTime, string $endTime)
    {
        $this->start = Carbon::parse($startTime);
        $this->end   = Carbon::parse($endTime);

        if ($this->end->lte($this->start)) {
            throw new InvalidArgumentException(
                "終了時刻({$endTime})は開始時刻({$startTime})より後でなければなりません。"
            );
        }
    }

    public function startTime(): Carbon
    {
        return $this->start->copy();
    }

    public function endTime(): Carbon
    {
        return $this->end->copy();
    }

    /** 分単位の合計時間 */
    public function durationMinutes(): int
    {
        return (int) $this->start->diffInMinutes($this->end);
    }

    /**
     * 指定された分数でスロットに分割した時間帯の配列を返す
     *
     * @return array<int, array{start: string, end: string}>
     */
    public function splitIntoSlots(int $durationMinutes): array
    {
        if ($durationMinutes <= 0) {
            throw new InvalidArgumentException('スロット時間は1分以上でなければなりません。');
        }

        $slots   = [];
        $current = $this->start->copy();

        while ($current->lt($this->end)) {
            $slotEnd = $current->copy()->addMinutes($durationMinutes);

            if ($slotEnd->lte($this->end)) {
                $slots[] = [
                    'start' => $current->format('H:i:s'),
                    'end'   => $slotEnd->format('H:i:s'),
                ];
            }

            $current->addMinutes($durationMinutes);
        }

        return $slots;
    }

    /** 開始時刻・終了時刻・スロット時間が変更されたか判定 */
    public function isDifferentFrom(string $otherStart, string $otherEnd): bool
    {
        return $this->start->format('H:i:s') !== Carbon::parse($otherStart)->format('H:i:s')
            || $this->end->format('H:i:s') !== Carbon::parse($otherEnd)->format('H:i:s');
    }

    public function startTimeString(): string
    {
        return $this->start->format('H:i:s');
    }

    public function endTimeString(): string
    {
        return $this->end->format('H:i:s');
    }
}
