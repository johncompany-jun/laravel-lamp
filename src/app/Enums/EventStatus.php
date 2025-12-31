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
            self::DRAFT => 'events.draft',
            self::OPEN => 'events.open',
            self::CLOSED => 'events.closed',
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

    /**
     * Get badge configuration for display.
     */
    public function badgeConfig(): array
    {
        return match($this) {
            self::DRAFT => [
                'bg' => '#F3F4F6',
                'color' => '#374151',
                'icon' => 'edit',
            ],
            self::OPEN => [
                'bg' => '#D1FAE5',
                'color' => '#065F46',
                'icon' => 'check_circle',
            ],
            self::CLOSED => [
                'bg' => '#FEE2E2',
                'color' => '#991B1B',
                'icon' => 'cancel',
            ],
            self::COMPLETED => [
                'bg' => '#DBEAFE',
                'color' => '#1E40AF',
                'icon' => 'done_all',
            ],
        };
    }
}
