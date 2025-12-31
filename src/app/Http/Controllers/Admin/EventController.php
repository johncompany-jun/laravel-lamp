<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Http\Requests\Admin\StoreEventAssignmentsRequest;
use App\Models\Event;
use App\Services\EventQueryService;
use App\UseCases\CreateEventUseCase;
use App\UseCases\UpdateEventUseCase;
use App\UseCases\StoreEventAssignmentsUseCase;

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
    public function store(StoreEventRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();

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
    public function update(UpdateEventRequest $request, Event $event)
    {
        $validated = $request->validated();

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
    public function storeAssignments(StoreEventAssignmentsRequest $request, Event $event)
    {
        $validated = $request->validated();

        $this->storeAssignmentsUseCase->execute($event, $validated['assignments']);

        return redirect()->route('admin.events.index')
            ->with('success', 'Assignments created successfully!');
    }
}
