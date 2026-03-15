<?php

namespace App\Http\Controllers;

use App\Domain\Event\Repositories\EventAssignmentRepositoryInterface;
use App\Http\Requests\SubmitEventApplicationRequest;
use App\Models\Event;
use App\Services\EventApplicationService;
use App\Services\EventQueryService;
use App\UseCases\CancelEventApplicationUseCase;
use App\UseCases\CheckEventViewPermissionUseCase;
use App\UseCases\SubmitEventApplicationUseCase;

class EventController extends Controller
{
    public function __construct(
        private EventQueryService               $queryService,
        private EventApplicationService         $applicationService,
        private SubmitEventApplicationUseCase   $submitApplicationUseCase,
        private CancelEventApplicationUseCase   $cancelApplicationUseCase,
        private CheckEventViewPermissionUseCase $checkViewPermission,
        private EventAssignmentRepositoryInterface $assignmentRepository,
    ) {}

    /**
     * Display a listing of open events.
     */
    public function index()
    {
        $events = $this->queryService->getOpenEvents();

        return view('events.index', compact('events'));
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        $user = auth()->user();

        $this->checkViewPermission->execute($event, $user);

        $event                = $this->queryService->getEventWithApplicationSlots($event);
        $existingApplications = $this->applicationService->getUserApplications($user, $event);

        return view('events.show', compact('event', 'existingApplications'));
    }

    /**
     * Store a new event application.
     */
    public function apply(SubmitEventApplicationRequest $request, Event $event)
    {
        $user = auth()->user();
        $slots = $request->input('slots', []);
        $validated = $request->validated();

        try {
            $this->submitApplicationUseCase->execute(
                $user,
                $event,
                $slots,
                $request->input('can_help_setup', false),
                $request->input('can_help_cleanup', false),
                $request->input('can_transport_by_car', false),
                $validated['comment'] ?? null
            );

            return redirect()->route('dashboard')
                ->with('success', 'Application submitted successfully!');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['slots' => $e->getMessage()]);
        }
    }

    /**
     * Cancel an event application.
     */
    public function cancelApplication(Event $event)
    {
        $user = auth()->user();

        try {
            $this->cancelApplicationUseCase->execute($event, $user);

            return redirect()->route('dashboard')
                ->with('success', 'Application cancelled successfully!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Unauthorized action.');
        }
    }

    /**
     * View event assignments (read-only).
     */
    public function viewAssignments(Event $event)
    {
        $user = auth()->user();

        if (!$this->assignmentRepository->existsByEventAndUser($event->id, $user->id)) {
            abort(403, 'You do not have permission to view this event\'s assignments.');
        }

        $event->load(['slots']);

        $existingAssignments = $this->assignmentRepository->getByEvent($event);
        $locations           = $event->locations ?? [];
        $slotMatrix          = $this->buildSlotMatrix($event->slots);

        return view('events.assignments.view', compact('event', 'slotMatrix', 'locations', 'existingAssignments'));
    }

    private function buildSlotMatrix(\Illuminate\Database\Eloquent\Collection $slots): array
    {
        $matrix = [];

        foreach ($slots as $slot) {
            $timeKey     = date('H:i', strtotime($slot->start_time)) . '-' . date('H:i', strtotime($slot->end_time));
            $locationKey = $slot->location ?? 'default';

            $matrix[$timeKey][$locationKey] = $slot;
        }

        return $matrix;
    }
}
