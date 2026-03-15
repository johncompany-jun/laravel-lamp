<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Event\Repositories\EventApplicationRepositoryInterface;
use App\Models\Event;
use App\Models\EventApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class EloquentEventApplicationRepository implements EventApplicationRepositoryInterface
{
    public function existsByEventAndUser(int $eventId, int $userId): bool
    {
        return EventApplication::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function getByEventGroupedByUser(Event $event): \Illuminate\Support\Collection
    {
        return EventApplication::where('event_id', $event->id)
            ->with(['user', 'applicationSlot'])
            ->get()
            ->groupBy('user_id');
    }

    public function getByUserAndEvent(User $user, Event $event): Collection
    {
        return EventApplication::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->with('applicationSlot')
            ->get();
    }

    public function getSortedByTimeForUserAndEvent(User $user, Event $event): Collection
    {
        return $this->getByUserAndEvent($user, $event)
            ->sortBy(fn($app) => $app->applicationSlot->start_time)
            ->values();
    }

    public function save(EventApplication $application): EventApplication
    {
        $application->save();
        return $application;
    }

    public function deleteByUserAndEvent(User $user, Event $event): void
    {
        EventApplication::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    public function updateCapabilitiesByUserAndEvent(int $eventId, int $userId, array $data): void
    {
        EventApplication::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->update($data);
    }

    public function getDashboardApplicationsForUser(User $user, int $limit): \Illuminate\Support\Collection
    {
        return EventApplication::where('user_id', $user->id)
            ->with(['event.applicationSlots', 'applicationSlot'])
            ->latest()
            ->get()
            ->groupBy('event_id')
            ->map(function ($applications) {
                return [
                    'event'        => $applications->first()->event,
                    'applications' => $applications->keyBy('event_application_slot_id'),
                    'applied_at'   => $applications->first()->created_at,
                ];
            })
            ->sortByDesc(fn($item) => $item['event']->event_date)
            ->take($limit);
    }
}
