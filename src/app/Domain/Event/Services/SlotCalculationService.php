<?php

namespace App\Domain\Event\Services;

use App\Domain\Event\ValueObjects\EventPeriod;

/**
 * スロット時間帯計算のドメインサービス
 *
 * 「期間をどう分割するか」という純粋なビジネスロジックのみを担当。
 * DB への書き込み等のインフラ処理は含まない。
 *
 * 既存の EventSlotGenerator（インフラ層）の計算部分をここに集約し、
 * EventSlotGenerator はこのサービスに委譲することで関心を分離する。
 */
final class SlotCalculationService
{
    /**
     * 申込スロットの時間帯リストを計算する
     *
     * @return array<int, array{start: string, end: string}>
     */
    public function calculateApplicationSlots(EventPeriod $period, int $durationMinutes): array
    {
        return $period->splitIntoSlots($durationMinutes);
    }

    /**
     * アサインメントスロットの時間帯リストを計算する（場所ごとに展開）
     *
     * @param string[] $locations
     * @return array<int, array{start: string, end: string, location: string|null}>
     */
    public function calculateAssignmentSlots(
        EventPeriod $period,
        int         $durationMinutes,
        array       $locations = [],
        int         $capacity  = 3,
    ): array {
        $timeRanges = $period->splitIntoSlots($durationMinutes);
        $slots      = [];

        foreach ($timeRanges as $range) {
            if (!empty($locations)) {
                foreach ($locations as $location) {
                    $slots[] = [
                        'start'    => $range['start'],
                        'end'      => $range['end'],
                        'location' => $location,
                        'capacity' => $capacity,
                    ];
                }
            } else {
                $slots[] = [
                    'start'    => $range['start'],
                    'end'      => $range['end'],
                    'location' => null,
                    'capacity' => $capacity,
                ];
            }
        }

        return $slots;
    }

    /**
     * 申込スロットの再生成が必要か判定する
     *
     * 開始時刻・終了時刻・スロット時間のいずれかが変わった場合に再生成が必要
     */
    public function needsApplicationSlotRegeneration(
        EventPeriod $current,
        string      $newStart,
        string      $newEnd,
        int         $currentDuration,
        int         $newDuration,
    ): bool {
        return $current->isDifferentFrom($newStart, $newEnd)
            || $currentDuration !== $newDuration;
    }

    /**
     * アサインメントスロットの再生成が必要か判定する
     *
     * @param string[] $currentLocations
     * @param string[] $newLocations
     */
    public function needsAssignmentSlotRegeneration(
        EventPeriod $current,
        string      $newStart,
        string      $newEnd,
        int         $currentDuration,
        int         $newDuration,
        array       $currentLocations,
        array       $newLocations,
    ): bool {
        $locationsChanged = json_encode($currentLocations) !== json_encode($newLocations);

        return $current->isDifferentFrom($newStart, $newEnd)
            || $currentDuration !== $newDuration
            || $locationsChanged;
    }
}
