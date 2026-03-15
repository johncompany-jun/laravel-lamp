<?php

namespace App\Http\Controllers;

use App\Domain\Event\Repositories\EventApplicationRepositoryInterface;
use App\Domain\Event\Repositories\EventAssignmentRepositoryInterface;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Enums\EventStatus;

class DashboardController extends Controller
{
    public function __construct(
        private EventRepositoryInterface            $eventRepository,
        private EventApplicationRepositoryInterface $applicationRepository,
        private EventAssignmentRepositoryInterface  $assignmentRepository,
    ) {}

    public function index()
    {
        $user = auth()->user();

        $openEvents = $this->eventRepository->getUpcomingOpen(5);

        $myApplications = $this->applicationRepository->getDashboardApplicationsForUser($user, 5);

        $myAssignments = $this->assignmentRepository->getUpcomingForUser(
            $user,
            [EventStatus::CLOSED, EventStatus::CANCELLED],
            5
        );

        return view('dashboard', compact('openEvents', 'myApplications', 'myAssignments'));
    }
}
