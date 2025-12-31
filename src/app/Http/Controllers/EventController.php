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
        // Only allow users to view open events
        if ($event->status !== EventStatus::OPEN) {
            abort(404);
        }

        $event = $this->queryService->getEventWithApplicationSlots($event);
        $user = auth()->user();
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
}
