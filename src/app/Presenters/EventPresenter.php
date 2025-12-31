<?php

namespace App\Presenters;

use App\Models\Event;

class EventPresenter
{
    public function __construct(
        private Event $event
    ) {}

    /**
     * Get formatted date.
     */
    public function formattedDate(): string
    {
        return $this->event->event_date->format('Y-m-d');
    }

    /**
     * Get formatted time range.
     */
    public function timeRange(): string
    {
        $start = date('H:i', strtotime($this->event->start_time));
        $end = date('H:i', strtotime($this->event->end_time));

        return "{$start} - {$end}";
    }

    /**
     * Get status badge HTML.
     */
    public function statusBadge(): string
    {
        $config = $this->event->status->badgeConfig();
        $label = $this->event->status->translatedLabel();

        return sprintf(
            '<span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; background-color: %s; color: %s; border-radius: 16px; font-size: 12px; font-weight: 500;">
                <span class="material-icons" style="font-size: 14px;">%s</span>
                %s
            </span>',
            $config['bg'],
            $config['color'],
            $config['icon'],
            $label
        );
    }

    /**
     * Get recurring badge HTML.
     */
    public function recurringBadge(): string
    {
        if (!$this->event->is_recurring) {
            return '';
        }

        return '<span style="display: inline-flex; align-items: center; gap: 2px; padding: 2px 8px; background-color: #DBEAFE; color: #1E40AF; border-radius: 12px; font-size: 11px; font-weight: 500;">
            <span class="material-icons" style="font-size: 12px;">repeat</span>
            ' . __('events.recurring') . '
        </span>';
    }

    /**
     * Get template badge HTML.
     */
    public function templateBadge(): string
    {
        if (!$this->event->is_template) {
            return '';
        }

        return '<span style="display: inline-flex; align-items: center; gap: 2px; padding: 2px 8px; background-color: #E9D5FF; color: #6B21A8; border-radius: 12px; font-size: 11px; font-weight: 500;">
            <span class="material-icons" style="font-size: 12px;">description</span>
            ' . __('events.template') . '
        </span>';
    }

    /**
     * Get all badges HTML.
     */
    public function badges(): string
    {
        $badges = array_filter([
            $this->recurringBadge(),
            $this->templateBadge()
        ]);

        if (empty($badges)) {
            return '';
        }

        return '<div style="display: flex; gap: 4px; margin-top: 4px;">' .
               implode('', $badges) .
               '</div>';
    }

    /**
     * Get applications count.
     */
    public function applicationsCount(): int
    {
        return $this->event->applications()->count();
    }

    /**
     * Get locations display.
     */
    public function locationsDisplay(): string
    {
        $locations = $this->event->locations ?? [];

        if (empty($locations)) {
            return __('events.not_set');
        }

        return implode(', ', $locations);
    }
}
