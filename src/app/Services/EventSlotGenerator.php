<?php

namespace App\Services;

use App\Domain\Event\Services\SlotCalculationService;
use App\Domain\Event\ValueObjects\EventPeriod;
use App\Models\Event;

/**
 * スロット生成のインフラサービス
 *
 * ビジネスロジック（計算）は SlotCalculationService に委譲し、
 * このクラスは DB への書き込みのみを担当する。
 */
class EventSlotGenerator
{
    public function __construct(
        private readonly SlotCalculationService $slotCalculation,
    ) {}

    /**
     * 申込スロットを生成して DB に保存する
     */
    public function generateApplicationSlots(Event $event): void
    {
        $period   = new EventPeriod($event->start_time, $event->end_time);
        $duration = $event->application_slot_duration->value;

        $slots = array_map(
            fn($range) => [
                'event_id'   => $event->id,
                'start_time' => $range['start'],
                'end_time'   => $range['end'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            $this->slotCalculation->calculateApplicationSlots($period, $duration),
        );

        if (!empty($slots)) {
            $event->applicationSlots()->insert($slots);
        }
    }

    /**
     * アサインメントスロットを生成して DB に保存する
     */
    public function generateTimeSlots(Event $event): void
    {
        $period    = new EventPeriod($event->start_time, $event->end_time);
        $duration  = $event->slot_duration->value;
        $locations = $event->locations ?? [];

        $slots = array_map(
            fn($slot) => [
                'event_id'   => $event->id,
                'start_time' => $slot['start'],
                'end_time'   => $slot['end'],
                'location'   => $slot['location'],
                'capacity'   => $slot['capacity'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            $this->slotCalculation->calculateAssignmentSlots($period, $duration, $locations),
        );

        if (!empty($slots)) {
            $event->slots()->insert($slots);
        }
    }

    /**
     * 申込スロットの再生成が必要か判定する
     */
    public function needsApplicationSlotRegeneration(Event $event, array $newData): bool
    {
        $period          = new EventPeriod($event->start_time, $event->end_time);
        $currentDuration = $event->application_slot_duration->value;
        $newDuration     = is_int($newData['application_slot_duration'])
            ? $newData['application_slot_duration']
            : $newData['application_slot_duration']->value;

        return $this->slotCalculation->needsApplicationSlotRegeneration(
            $period,
            $newData['start_time'],
            $newData['end_time'],
            $currentDuration,
            $newDuration,
        );
    }

    /**
     * アサインメントスロットの再生成が必要か判定する
     */
    public function needsTimeSlotRegeneration(Event $event, array $newData): bool
    {
        $period          = new EventPeriod($event->start_time, $event->end_time);
        $currentDuration = $event->slot_duration->value;
        $newDuration     = is_int($newData['slot_duration'])
            ? $newData['slot_duration']
            : $newData['slot_duration']->value;

        return $this->slotCalculation->needsAssignmentSlotRegeneration(
            $period,
            $newData['start_time'],
            $newData['end_time'],
            $currentDuration,
            $newDuration,
            $event->locations ?? [],
            $newData['locations'] ?? [],
        );
    }

    /**
     * 申込がなければ申込スロットを再生成する
     */
    public function regenerateApplicationSlotsIfSafe(Event $event): bool
    {
        if ($event->applicationSlots()->whereHas('applications')->exists()) {
            return false;
        }

        $event->applicationSlots()->delete();
        $this->generateApplicationSlots($event);

        return true;
    }

    /**
     * アサインメントがなければアサインメントスロットを再生成する
     */
    public function regenerateTimeSlotsIfSafe(Event $event): bool
    {
        if ($event->slots()->whereHas('assignments')->exists()) {
            return false;
        }

        $event->slots()->delete();
        $this->generateTimeSlots($event);

        return true;
    }
}
