<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Event\Repositories\EventAssignmentRepositoryInterface;
use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class EloquentEventAssignmentRepository implements EventAssignmentRepositoryInterface
{
    public function existsByEventAndUser(int $eventId, int $userId): bool
    {
        return EventAssignment::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function getByEvent(Event $event): Collection
    {
        return EventAssignment::where('event_id', $event->id)
            ->with(['user', 'slot'])
            ->get();
    }

    public function getSpecialByEvent(Event $event): Collection
    {
        return EventAssignment::where('event_id', $event->id)
            ->whereNotNull('special_role')
            ->with(['user'])
            ->get();
    }

    public function create(array $data): EventAssignment
    {
        return EventAssignment::create($data);
    }

    public function deleteByEvent(Event $event): void
    {
        EventAssignment::where('event_id', $event->id)->delete();
    }

    public function getUpcomingForUser(User $user, array $statuses, int $limit): Collection
    {
        return EventAssignment::where('user_id', $user->id)
            ->with(['event', 'slot'])
            ->whereHas('event', function ($query) use ($statuses) {
                $query->where('event_date', '>=', today())
                    ->whereIn('status', $statuses);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
