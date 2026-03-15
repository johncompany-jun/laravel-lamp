<?php

namespace App\Enums;

/**
 * スロットの参加可否を表すEnum
 *
 * 申込時にユーザーが各スロットに対して申告する可否状態。
 * 従来の magic string ('available' / 'unavailable') をEnum化したもの。
 */
enum SlotAvailability: string
{
    case AVAILABLE   = 'available';
    case UNAVAILABLE = 'unavailable';

    public function isAvailable(): bool
    {
        return $this === self::AVAILABLE;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
