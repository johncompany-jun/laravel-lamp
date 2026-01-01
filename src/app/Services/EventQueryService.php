<?php

namespace App\Services;

use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EventQueryService
{
    public function __construct(
        private EventRepositoryInterface $eventRepository
    ) {}

    /**
     * Get open events for public display.
     */
    public function getOpenEvents(int $perPage = 10): LengthAwarePaginator
    {
        return $this->eventRepository->getOpenEvents($perPage);
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
        return $this->eventRepository->findWithApplicationSlots($event);
    }

    /**
     * Get event with all relationships for admin display.
     */
    public function getEventWithFullDetails(Event $event): Event
    {
        // Load child events separately (not in repository interface)
        return $this->eventRepository->findWithFullDetails($event)->load('childEvents');
    }

    /**
     * Get event with slots and applications for assignments page.
     */
    public function getEventWithSlotsAndApplications(Event $event): Event
    {
        return $this->eventRepository->findWithSlotsAndApplications($event);
    }

    /**
     * Get template events.
     */
    public function getTemplateEvents(?int $excludeId = null): Collection
    {
        return $this->eventRepository->getTemplateEvents($excludeId);
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
