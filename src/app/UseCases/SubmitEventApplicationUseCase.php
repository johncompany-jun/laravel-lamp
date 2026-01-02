<?php

namespace App\UseCases;

use App\Models\Event;
use App\Models\User;
use App\Services\EventApplicationService;

class SubmitEventApplicationUseCase
{
    public function __construct(
        private EventApplicationService $applicationService
    ) {}

    /**
     * Execute the use case to submit an event application.
     *
     * @throws \InvalidArgumentException
     */
    public function execute(
        User $user,
        Event $event,
        array $slots,
        bool $canHelpSetup = false,
        bool $canHelpCleanup = false,
        bool $canTransportByCar = false,
        ?string $comment = null
    ): void {
        // Validate and filter slots
        $validSlots = $this->applicationService->validateSlots($slots);

        if (empty($validSlots)) {
            throw new \InvalidArgumentException('Please select at least one time slot.');
        }

        // Submit the application
        $this->applicationService->submitApplication(
            $user,
            $event,
            $validSlots,
            $canHelpSetup,
            $canHelpCleanup,
            $canTransportByCar,
            $comment
        );
    }
}
