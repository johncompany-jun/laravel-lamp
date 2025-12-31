<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\EventQueryService;
use App\UseCases\CreateEventUseCase;
use App\UseCases\UpdateEventUseCase;
use App\UseCases\StoreEventAssignmentsUseCase;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private EventQueryService $queryService,
        private CreateEventUseCase $createEventUseCase,
        private UpdateEventUseCase $updateEventUseCase,
        private StoreEventAssignmentsUseCase $storeAssignmentsUseCase
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = $this->queryService->getParentEvents();

        return view('admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $templates = $this->queryService->getTemplateEvents();

        return view('admin.events.create', compact('templates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateEventData($request);
        $validated['created_by'] = auth()->id();
        $validated['slot_duration'] = (int) $validated['slot_duration'];
        $validated['application_slot_duration'] = (int) $validated['application_slot_duration'];

        $this->createEventUseCase->execute($validated);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event = $this->queryService->getEventWithFullDetails($event);

        return view('admin.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $templates = $this->queryService->getTemplateEvents($event->id);

        return view('admin.events.edit', compact('event', 'templates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $this->validateEventData($request, false);
        $validated['slot_duration'] = (int) $validated['slot_duration'];
        $validated['application_slot_duration'] = (int) $validated['application_slot_duration'];

        $this->updateEventUseCase->execute($event, $validated);

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Event updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted successfully!');
    }

    /**
     * Show the assignment creation page for an event.
     */
    public function createAssignments(Event $event)
    {
        $event = $this->queryService->getEventWithSlotsAndApplications($event);
        $applications = $this->queryService->getApplicationsGroupedByUser($event);
        $existingAssignments = $this->queryService->getExistingAssignments($event);

        return view('admin.events.assignments.create', compact('event', 'applications', 'existingAssignments'));
    }

    /**
     * Store assignments for an event.
     */
    public function storeAssignments(Request $request, Event $event)
    {
        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.slot_id' => 'required|exists:event_slots,id',
            'assignments.*.user_id' => 'required|exists:users,id',
            'assignments.*.role' => 'required|in:participant,leader',
        ]);

        $this->storeAssignmentsUseCase->execute($event, $validated['assignments']);

        return redirect()->route('admin.events.index')
            ->with('success', 'Assignments created successfully!');
    }

    /**
     * Validate event data from request.
     */
    private function validateEventData(Request $request, bool $isCreate = true): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|in:10,20,30',
            'application_slot_duration' => 'required|in:30,60,90,120',
            'location' => 'nullable|string|max:255',
            'locations' => 'nullable|array|max:3',
            'locations.*' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,open,closed,completed',
            'is_template' => 'boolean',
        ];

        if ($isCreate) {
            $rules['is_recurring'] = 'boolean';
            $rules['recurrence_type'] = 'nullable|required_if:is_recurring,true|in:weekly';
            $rules['recurrence_end_date'] = 'nullable|required_if:is_recurring,true|date|after:event_date';
        }

        return $request->validate($rules);
    }
}
