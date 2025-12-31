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
    public function getParentEvents(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = Event::with('creator')
            ->whereNull('parent_event_id');

        // Apply filters
        if (!empty($filters['location'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('location', 'like', '%' . $filters['location'] . '%')
                  ->orWhereJsonContains('locations', $filters['location']);
            });
        }

        if (!empty($filters['event_date'])) {
            $query->whereDate('event_date', $filters['event_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest('event_date')->paginate($perPage);
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
