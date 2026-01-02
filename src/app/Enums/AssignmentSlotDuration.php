<?php

namespace App\Enums;

enum AssignmentSlotDuration: int
{
    case TEN_MINUTES = 10;
    case FIFTEEN_MINUTES = 15;
    case TWENTY_MINUTES = 20;
    case THIRTY_MINUTES = 30;

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
            self::TEN_MINUTES => '10' . __('events.minutes'),
            self::FIFTEEN_MINUTES => '15' . __('events.minutes'),
            self::TWENTY_MINUTES => '20' . __('events.minutes'),
            self::THIRTY_MINUTES => '30' . __('events.minutes'),
        };
    }
}
