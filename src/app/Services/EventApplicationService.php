<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventApplication;
use App\Models\User;
use Illuminate\Support\Collection;

class EventApplicationService
{
    /**
     * Validate slot data from request.
     */
    public function validateSlots(array $slots): array
    {
        $validSlots = [];

        foreach ($slots as $slotId => $slotData) {
            if (isset($slotData['slot_id']) && isset($slotData['availability'])) {
                $validSlots[] = [
                    'slot_id' => $slotData['slot_id'],
                    'availability' => $slotData['availability'],
                ];
            }
        }

        return $validSlots;
    }

    /**
     * Submit application for an event.
     */
    public function submitApplication(
        User $user,
        Event $event,
        array $validSlots,
        bool $canHelpSetup = false,
        bool $canHelpCleanup = false,
        ?string $comment = null
    ): void {
        // Delete existing applications for this event
        $this->deleteUserApplications($user, $event);

        // Create new applications for each selected slot
        foreach ($validSlots as $slotData) {
            EventApplication::create([
                'event_id' => $event->id,
                'event_application_slot_id' => $slotData['slot_id'],
                'user_id' => $user->id,
                'availability' => $slotData['availability'],
                'can_help_setup' => $canHelpSetup,
                'can_help_cleanup' => $canHelpCleanup,
                'comment' => $comment,
            ]);
        }
    }

    /**
     * Delete all applications for a user and event.
     */
    public function deleteUserApplications(User $user, Event $event): void
    {
        EventApplication::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * Get user's existing applications for an event.
     */
    public function getUserApplications(User $user, Event $event): Collection
    {
        return EventApplication::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->with('applicationSlot')
            ->get()
            ->keyBy('event_application_slot_id');
    }

    /**
     * Get all user's applications for an event, sorted by time.
     */
    public function getUserApplicationsSortedByTime(User $user, Event $event): Collection
    {
        return EventApplication::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->with('applicationSlot')
            ->get()
            ->sortBy(function ($app) {
                return $app->applicationSlot->start_time;
            })
            ->values();
    }

    /**
     * Cancel a specific application and update related applications.
     */
    public function cancelApplication(EventApplication $application, User $user): bool
    {
        // Ensure the application belongs to the authenticated user
        if ($application->user_id !== $user->id) {
            return false;
        }

        // Get all applications for this event by this user, ordered by time slot
        $allUserApplications = $this->getUserApplicationsSortedByTime($user, $application->event);

        // Check if this is the first or last slot
        $isFirstSlot = $allUserApplications->first()->id === $application->id;
        $isLastSlot = $allUserApplications->last()->id === $application->id;

        // Delete the application
        $application->delete();

        // Update remaining applications if needed
        if ($allUserApplications->count() > 1) {
            $this->updateApplicationsAfterCancellation(
                $application,
                $user,
                $isFirstSlot,
                $isLastSlot
            );
        }

        return true;
    }

    /**
     * Update remaining applications after cancellation.
     */
    private function updateApplicationsAfterCancellation(
        EventApplication $cancelledApplication,
        User $user,
        bool $wasFirstSlot,
        bool $wasLastSlot
    ): void {
        // If we cancelled the first slot and it had setup help, remove setup from all remaining
        if ($wasFirstSlot && $cancelledApplication->can_help_setup) {
            EventApplication::where('event_id', $cancelledApplication->event_id)
                ->where('user_id', $user->id)
                ->update(['can_help_setup' => false]);
        }

        // If we cancelled the last slot and it had cleanup help, remove cleanup from all remaining
        if ($wasLastSlot && $cancelledApplication->can_help_cleanup) {
            EventApplication::where('event_id', $cancelledApplication->event_id)
                ->where('user_id', $user->id)
                ->update(['can_help_cleanup' => false]);
        }
    }
}
