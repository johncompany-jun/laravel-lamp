<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Event Details') }}
            </h2>
            <div class="flex gap-3">
                <a href="{{ route('admin.events.edit', $event) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    Edit Event
                </a>
                <a href="{{ route('admin.events.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Event Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Event Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $event->title }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $event->event_date->format('F d, Y (l)') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Time</label>
                            <p class="mt-1 text-sm text-gray-900">{{ date('H:i', strtotime($event->start_time)) }} - {{ date('H:i', strtotime($event->end_time)) }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Slot Duration</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $event->slot_duration }} minutes</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Location</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $event->location ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <p class="mt-1">
                                @if($event->status === 'draft')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Draft
                                    </span>
                                @elseif($event->status === 'open')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Open
                                    </span>
                                @elseif($event->status === 'closed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Closed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Completed
                                    </span>
                                @endif
                            </p>
                        </div>

                        @if($event->is_recurring)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Recurrence</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    Weekly until {{ \Carbon\Carbon::parse($event->recurrence_end_date)->format('F d, Y') }}
                                </span>
                                @if($event->childEvents->count() > 0)
                                    <span class="ml-2 text-gray-600">({{ $event->childEvents->count() }} recurring instances created)</span>
                                @endif
                            </p>
                        </div>
                        @endif

                        @if($event->is_template)
                        <div class="md:col-span-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                Template Event
                            </span>
                        </div>
                        @endif

                        @if($event->notes)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $event->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Time Slots -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Time Slots ({{ $event->slots->count() }})</h3>

                    @if($event->slots->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($event->slots as $slot)
                                <div class="border rounded-lg p-4 {{ $slot->isFull() ? 'bg-gray-50' : 'bg-white' }}">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium text-gray-900">
                                                {{ date('H:i', strtotime($slot->start_time)) }} - {{ date('H:i', strtotime($slot->end_time)) }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Capacity: {{ $slot->assignments->count() }} / {{ $slot->capacity }}
                                            </p>
                                        </div>
                                        @if($slot->isFull())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                Full
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Available
                                            </span>
                                        @endif
                                    </div>

                                    @if($slot->assignments->count() > 0)
                                        <div class="mt-3 pt-3 border-t">
                                            <p class="text-xs font-medium text-gray-700 mb-1">Assigned:</p>
                                            @foreach($slot->assignments as $assignment)
                                                <p class="text-sm text-gray-600">{{ $assignment->user->name }}</p>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No time slots available.</p>
                    @endif
                </div>
            </div>

            <!-- Applications -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Applications ({{ $event->applications->count() }})</h3>

                    @if($event->applications->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Availability</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied At</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($event->applications as $application)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $application->user->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($application->availability === 'available')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        Available
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        Unavailable
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ $application->comment ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $application->created_at->format('M d, Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">No applications yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
