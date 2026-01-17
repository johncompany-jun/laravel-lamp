<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventAssignment;

class EventAssignmentService
{
    /**
     * Delete all assignments for an event.
     */
    public function deleteEventAssignments(Event $event): void
    {
        EventAssignment::where('event_id', $event->id)->delete();
    }

    /**
     * Create assignments for an event.
     */
    public function createAssignments(Event $event, array $assignments): void
    {
        foreach ($assignments as $assignment) {
            EventAssignment::create([
                'event_id' => $event->id,
                'event_slot_id' => $assignment['slot_id'] ?? null,
                'user_id' => $assignment['user_id'],
                'role' => $assignment['role'],
                'special_role' => $assignment['special_role'] ?? null,
                'assigned_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Replace all assignments for an event.
     */
    public function replaceAssignments(Event $event, array $assignments): void
    {
        $this->deleteEventAssignments($event);
        $this->createAssignments($event, $assignments);
    }
}
