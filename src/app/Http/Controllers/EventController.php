<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Http\Requests\SubmitEventApplicationRequest;
use App\Models\Event;
use App\Models\EventApplication;
use App\Services\EventApplicationService;
use App\Services\EventQueryService;
use App\UseCases\CancelEventApplicationUseCase;
use App\UseCases\SubmitEventApplicationUseCase;

class EventController extends Controller
{
    public function __construct(
        private EventQueryService $queryService,
        private EventApplicationService $applicationService,
        private SubmitEventApplicationUseCase $submitApplicationUseCase,
        private CancelEventApplicationUseCase $cancelApplicationUseCase
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

        // Do not allow viewing completed events
        if ($event->status === EventStatus::COMPLETED) {
            abort(404);
        }

        // Allow viewing if:
        // 1. Event is open, OR
        // 2. User has applied to this event, OR
        // 3. User has been assigned to this event
        $hasApplied = EventApplication::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->exists();

        $hasAssignment = \App\Models\EventAssignment::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($event->status !== EventStatus::OPEN && !$hasApplied && !$hasAssignment) {
            abort(404);
        }

        $event = $this->queryService->getEventWithApplicationSlots($event);
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
    public function cancelApplication(EventApplication $application)
    {
        $user = auth()->user();

        try {
            $this->cancelApplicationUseCase->execute($application, $user);

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

        // Only allow viewing if user has been assigned to this event
        $hasAssignment = \App\Models\EventAssignment::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$hasAssignment) {
            abort(403, 'You do not have permission to view this event\'s assignments.');
        }

        // Load event with slots and assignments
        $event->load(['slots.assignments.user']);

        // Get existing assignments grouped by slot
        $existingAssignments = \App\Models\EventAssignment::where('event_id', $event->id)
            ->with('user')
            ->get();

        // Build slot matrix for display
        $locations = $event->locations ?? [];
        $slotMatrix = [];

        foreach ($event->slots as $slot) {
            $timeKey = date('H:i', strtotime($slot->start_time)) . '-' . date('H:i', strtotime($slot->end_time));
            $locationKey = $slot->location ?? 'default';

            if (!isset($slotMatrix[$timeKey])) {
                $slotMatrix[$timeKey] = [];
            }

            $slotMatrix[$timeKey][$locationKey] = $slot;
        }

        return view('events.assignments.view', compact('event', 'slotMatrix', 'locations', 'existingAssignments'));
    }
}
