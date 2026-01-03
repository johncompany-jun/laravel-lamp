<?php

namespace App\UseCases;

use App\Models\Event;
use App\Models\User;
use App\Services\EventApplicationService;

class CancelEventApplicationUseCase
{
    public function __construct(
        private EventApplicationService $applicationService
    ) {}

    /**
     * Execute the use case to cancel all event applications for a user and event.
     *
     * @throws \Exception
     */
    public function execute(Event $event, User $user): void
    {
        // Delete all applications for this event by this user
        $this->applicationService->deleteUserApplications($user, $event);
    }
}
