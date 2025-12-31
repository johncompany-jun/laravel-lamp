<?php

namespace App\UseCases;

use App\Models\Event;
use App\Services\EventAssignmentService;

class StoreEventAssignmentsUseCase
{
    public function __construct(
        private EventAssignmentService $assignmentService
    ) {}

    /**
     * Execute the use case to store event assignments.
     */
    public function execute(Event $event, array $assignments): void
    {
        $this->assignmentService->replaceAssignments($event, $assignments);
    }
}
