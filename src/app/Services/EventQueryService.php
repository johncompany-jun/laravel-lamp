<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EventQueryService
{
    /**
     * Get open events for public display.
     */
    public function getOpenEvents(int $perPage = 10): LengthAwarePaginator
    {
        return Event::where('status', 'open')
            ->whereNull('parent_event_id')
            ->where('event_date', '>=', today())
            ->orderBy('event_date')
            ->paginate($perPage);
    }

    /**
     * Get all parent events for admin.
     */
    public function getParentEvents(int $perPage = 20): LengthAwarePaginator
    {
        return Event::with('creator')
            ->whereNull('parent_event_id')
            ->latest('event_date')
            ->paginate($perPage);
    }

    /**
     * Get event with application slots for public display.
     */
    public function getEventWithApplicationSlots(Event $event): Event
    {
        return $event->load(['applicationSlots.applications']);
    }

    /**
     * Get event with all relationships for admin display.
     */
    public function getEventWithFullDetails(Event $event): Event
    {
        return $event->load(['slots.assignments.user', 'applications.user', 'childEvents']);
    }

    /**
     * Get event with slots and applications for assignments page.
     */
    public function getEventWithSlotsAndApplications(Event $event): Event
    {
        return $event->load([
            'slots',
            'applicationSlots.applications' => function ($query) {
                $query->with('user');
            }
        ]);
    }

    /**
     * Get template events.
     */
    public function getTemplateEvents(?int $excludeId = null): Collection
    {
        $query = Event::where('is_template', true)->orderBy('title');

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get();
    }

    /**
     * Get applications grouped by user for an event.
     */
    public function getApplicationsGroupedByUser(Event $event): \Illuminate\Support\Collection
    {
        return \App\Models\EventApplication::where('event_id', $event->id)
            ->with(['user', 'applicationSlot'])
            ->get()
            ->groupBy('user_id');
    }

    /**
     * Get existing assignments for an event.
     */
    public function getExistingAssignments(Event $event): Collection
    {
        return EventAssignment::where('event_id', $event->id)
            ->with(['user', 'slot'])
            ->get();
    }
}
