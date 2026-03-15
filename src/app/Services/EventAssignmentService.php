<?php

namespace App\Services;

use App\Domain\Event\Repositories\EventAssignmentRepositoryInterface;
use App\Models\Event;

/**
 * イベントアサインメントのインフラサービス
 *
 * DB 操作はすべて EventAssignmentRepository 経由で行う。
 */
class EventAssignmentService
{
    public function __construct(
        private readonly EventAssignmentRepositoryInterface $assignmentRepository,
    ) {}

    public function deleteEventAssignments(Event $event): void
    {
        $this->assignmentRepository->deleteByEvent($event);
    }

    public function createAssignments(Event $event, array $assignments): void
    {
        foreach ($assignments as $assignment) {
            $this->assignmentRepository->create([
                'event_id'     => $event->id,
                'event_slot_id' => $assignment['slot_id'] ?? null,
                'user_id'      => $assignment['user_id'],
                'role'         => $assignment['role'],
                'special_role' => $assignment['special_role'] ?? null,
                'assigned_by'  => auth()->id(),
            ]);
        }
    }

    public function replaceAssignments(Event $event, array $assignments): void
    {
        $this->deleteEventAssignments($event);
        $this->createAssignments($event, $assignments);
    }
}
