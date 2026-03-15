<?php

namespace App\Enums;

enum EventStatus: string
{
    case DRAFT = 'draft';
    case OPEN = 'open';
    case CLOSED = 'closed';
    case COMPLETED = 'completed';

    /**
     * Get all enum values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get label for translation.
     */
    public function label(): string
    {
        return match($this) {
            self::DRAFT     => 'events.draft',
            self::OPEN      => 'events.open',
            self::CLOSED    => 'events.closed',
            self::COMPLETED => 'events.completed',
        };
    }

    /**
     * Get translated label.
     */
    public function translatedLabel(): string
    {
        return __($this->label());
    }
}
