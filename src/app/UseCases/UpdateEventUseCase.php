<?php

namespace App\UseCases;

use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Mail\EventCancelledNotification;
use App\Mail\EventConfirmedNotification;
use App\Models\Event;
use App\Services\EventSlotGenerator;
use Illuminate\Support\Facades\Mail;

class UpdateEventUseCase
{
    public function __construct(
        private EventRepositoryInterface $eventRepository,
        private EventSlotGenerator $slotGenerator
    ) {}

    /**
     * Execute the use case to update an event.
     */
    public function execute(Event $event, array $data): Event
    {
        // Filter out empty locations
        if (isset($data['locations'])) {
            $data['locations'] = array_values(array_filter($data['locations'], fn($loc) => !empty($loc)));
        }

        // Check if status has changed
        $oldStatus = $event->status;
        $newStatus = $data['status'] ?? $oldStatus;

        // Check if regeneration is needed before updating
        $needsApplicationSlotRegeneration = $this->slotGenerator->needsApplicationSlotRegeneration($event, $data);
        $needsTimeSlotRegeneration = $this->slotGenerator->needsTimeSlotRegeneration($event, $data);

        // Update the event
        $event->fill($data);
        $event = $this->eventRepository->save($event);

        // Regenerate application slots if needed and safe
        if ($needsApplicationSlotRegeneration) {
            $this->slotGenerator->regenerateApplicationSlotsIfSafe($event);
        }

        // Regenerate time slots if needed and safe
        if ($needsTimeSlotRegeneration) {
            $this->slotGenerator->regenerateTimeSlotsIfSafe($event);
        }

        // Send email notifications if status changed
        // TODO: メール通知機能を有効化する場合は以下のコメントを外してください
        // if ($oldStatus !== $newStatus) {
        //     $this->sendStatusChangeNotifications($event, $newStatus);
        // }

        return $event;
    }

    /**
     * Send email notifications based on status change.
     */
    private function sendStatusChangeNotifications(Event $event, string $newStatus): void
    {
        // When status changes to 'closed' (confirmed), send email to assigned users
        if ($newStatus === 'closed') {
            $assignedUsers = $event->assignments()
                ->with('user')
                ->get()
                ->pluck('user')
                ->unique('id');

            foreach ($assignedUsers as $user) {
                Mail::to($user->email)->send(new EventConfirmedNotification($event, $user));
            }
        }

        // When status changes to 'completed' (cancelled), send email to all applicants
        if ($newStatus === 'completed') {
            $applicants = $event->applications()
                ->with('user')
                ->get()
                ->pluck('user')
                ->unique('id');

            foreach ($applicants as $user) {
                Mail::to($user->email)->send(new EventCancelledNotification($event, $user));
            }
        }
    }
}
