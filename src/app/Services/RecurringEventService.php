<?php

namespace App\Services;

use App\Domain\Event\ValueObjects\RecurrenceSchedule;
use App\Models\Event;
use Carbon\Carbon;

/**
 * 繰り返しイベント生成のインフラサービス
 *
 * 繰り返し日付の計算は RecurrenceSchedule ValueObject に委譲し、
 * このクラスは DB への保存とスロット生成のみを担当する。
 */
class RecurringEventService
{
    public function __construct(
        private readonly EventSlotGenerator $slotGenerator,
    ) {}

    /**
     * 親イベントを元に繰り返しイベントを生成して保存する
     */
    public function createRecurringEvents(Event $parentEvent, string $endDate): void
    {
        $schedule = RecurrenceSchedule::weekly($endDate);
        $dates    = $schedule->generateWeeklyDates(Carbon::parse($parentEvent->event_date));

        foreach ($dates as $date) {
            $this->createRecurringInstance($parentEvent, $date);
        }
    }

    /**
     * 繰り返しイベントの1件を生成して保存する
     */
    private function createRecurringInstance(Event $parentEvent, Carbon $date): Event
    {
        $recurringEvent                       = $parentEvent->replicate();
        $recurringEvent->event_date           = $date->format('Y-m-d');
        $recurringEvent->parent_event_id      = $parentEvent->id;
        $recurringEvent->is_recurring         = false;
        $recurringEvent->is_template          = false;
        $recurringEvent->recurrence_type      = null;
        $recurringEvent->recurrence_end_date  = null;
        $recurringEvent->save();

        $this->slotGenerator->generateApplicationSlots($recurringEvent);
        $this->slotGenerator->generateTimeSlots($recurringEvent);

        return $recurringEvent;
    }
}
