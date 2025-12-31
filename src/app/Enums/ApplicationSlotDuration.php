<?php

namespace App\Enums;

enum ApplicationSlotDuration: int
{
    case THIRTY_MINUTES = 30;
    case ONE_HOUR = 60;
    case ONE_HALF_HOURS = 90;
    case TWO_HOURS = 120;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return $this->translatedLabel();
    }

    public function translatedLabel(): string
    {
        return match($this) {
            self::THIRTY_MINUTES => '30' . __('events.minutes'),
            self::ONE_HOUR => '1' . __('events.hour'),
            self::ONE_HALF_HOURS => '1.5' . __('events.hours'),
            self::TWO_HOURS => '2' . __('events.hours'),
        };
    }
}
