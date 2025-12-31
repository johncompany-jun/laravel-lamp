<?php

namespace App\UseCases;

use App\Models\EventApplication;
use App\Models\User;
use App\Services\EventApplicationService;

class CancelEventApplicationUseCase
{
    public function __construct(
        private EventApplicationService $applicationService
    ) {}

    /**
     * Execute the use case to cancel an event application.
     *
     * @throws \UnauthorizedAccessException
     */
    public function execute(EventApplication $application, User $user): void
    {
        $cancelled = $this->applicationService->cancelApplication($application, $user);

        if (!$cancelled) {
            throw new \Exception('Unauthorized action.');
        }
    }
}
