<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($event->slots()->whereHas('assignments')->exists())
                        <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                            <strong>Note:</strong> This event has assigned time slots. Time and slot duration cannot be changed.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.events.update', $event) }}" id="eventForm">
                        @csrf
                        @method('PUT')

                        <!-- Template Selection -->
                        @if($templates->count() > 0)
                        <div class="mb-6">
                            <label for="template_id" class="block font-medium text-sm text-gray-700">Use Template (Optional)</label>
                            <select id="template_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                <option value="">-- Select a template --</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}"
                                        data-title="{{ $template->title }}"
                                        data-start-time="{{ $template->start_time }}"
                                        data-end-time="{{ $template->end_time }}"
                                        data-slot-duration="{{ $template->slot_duration }}"
                                        data-location="{{ $template->location }}"
                                        data-notes="{{ $template->notes }}">
                                        {{ $template->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="block font-medium text-sm text-gray-700">Title</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $event->title) }}" required
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                            @error('title')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Event Date -->
                        <div class="mb-4">
                            <label for="event_date" class="block font-medium text-sm text-gray-700">Event Date</label>
                            <input type="date" name="event_date" id="event_date" value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" required
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                            @error('event_date')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Time Range -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="start_time" class="block font-medium text-sm text-gray-700">Start Time</label>
                                <input type="time" name="start_time" id="start_time"
                                    value="{{ old('start_time', substr($event->start_time, 0, 5)) }}"
                                    required
                                    {{ $event->slots()->whereHas('assignments')->exists() ? 'disabled' : '' }}
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                @if($event->slots()->whereHas('assignments')->exists())
                                    <input type="hidden" name="start_time" value="{{ substr($event->start_time, 0, 5) }}">
                                @endif
                                @error('start_time')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="end_time" class="block font-medium text-sm text-gray-700">End Time</label>
                                <input type="time" name="end_time" id="end_time"
                                    value="{{ old('end_time', substr($event->end_time, 0, 5)) }}"
                                    required
                                    {{ $event->slots()->whereHas('assignments')->exists() ? 'disabled' : '' }}
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                @if($event->slots()->whereHas('assignments')->exists())
                                    <input type="hidden" name="end_time" value="{{ substr($event->end_time, 0, 5) }}">
                                @endif
                                @error('end_time')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Slot Duration -->
                        <div class="mb-4">
                            <label for="slot_duration" class="block font-medium text-sm text-gray-700">Assignment Slot Duration (minutes)</label>
                            <select name="slot_duration" id="slot_duration" required
                                {{ $event->slots()->whereHas('assignments')->exists() ? 'disabled' : '' }}
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                <option value="10" {{ old('slot_duration', $event->slot_duration) == 10 ? 'selected' : '' }}>10 minutes</option>
                                <option value="20" {{ old('slot_duration', $event->slot_duration) == 20 ? 'selected' : '' }}>20 minutes</option>
                                <option value="30" {{ old('slot_duration', $event->slot_duration) == 30 ? 'selected' : '' }}>30 minutes</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Duration for admin to assign users to time slots</p>
                            @if($event->slots()->whereHas('assignments')->exists())
                                <input type="hidden" name="slot_duration" value="{{ $event->slot_duration }}">
                            @endif
                            @error('slot_duration')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Application Slot Duration -->
                        <div class="mb-4">
                            <label for="application_slot_duration" class="block font-medium text-sm text-gray-700">Application Slot Duration (minutes)</label>
                            <select name="application_slot_duration" id="application_slot_duration" required
                                {{ $event->applicationSlots()->whereHas('applications')->exists() ? 'disabled' : '' }}
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                <option value="30" {{ old('application_slot_duration', $event->application_slot_duration) == 30 ? 'selected' : '' }}>30 minutes</option>
                                <option value="60" {{ old('application_slot_duration', $event->application_slot_duration) == 60 ? 'selected' : '' }}>1 hour</option>
                                <option value="90" {{ old('application_slot_duration', $event->application_slot_duration) == 90 ? 'selected' : '' }}>1.5 hours</option>
                                <option value="120" {{ old('application_slot_duration', $event->application_slot_duration) == 120 ? 'selected' : '' }}>2 hours</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Duration for users to apply for availability</p>
                            @if($event->applicationSlots()->whereHas('applications')->exists())
                                <input type="hidden" name="application_slot_duration" value="{{ $event->application_slot_duration }}">
                            @endif
                            @error('application_slot_duration')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div class="mb-4">
                            <label for="location" class="block font-medium text-sm text-gray-700">Location</label>
                            <input type="text" name="location" id="location" value="{{ old('location', $event->location) }}"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                            @error('location')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Multiple Locations -->
                        <div class="mb-4">
                            <label class="block font-medium text-sm text-gray-700 mb-2">Locations for Assignment (2-3 areas)</label>
                            <div id="locationsContainer">
                                @php
                                    $existingLocations = old('locations', $event->locations ?? []);
                                    // Ensure we have at least 3 input fields
                                    $locationInputs = array_pad($existingLocations, 3, '');
                                @endphp
                                <div class="flex gap-2 mb-2">
                                    <input type="text" name="locations[]" placeholder="例: 北西" value="{{ $locationInputs[0] }}"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                </div>
                                <div class="flex gap-2 mb-2">
                                    <input type="text" name="locations[]" placeholder="例: 北東" value="{{ $locationInputs[1] }}"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                </div>
                                <div class="flex gap-2 mb-2">
                                    <input type="text" name="locations[]" placeholder="例: 南側 (optional)" value="{{ $locationInputs[2] }}"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Enter 2-3 location names for this event (e.g., 北西, 北東)</p>
                            @error('locations')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="block font-medium text-sm text-gray-700">Notes</label>
                            <textarea name="notes" id="notes" rows="4"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">{{ old('notes', $event->notes) }}</textarea>
                            @error('notes')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label for="status" class="block font-medium text-sm text-gray-700">Status</label>
                            <select name="status" id="status" required
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                <option value="draft" {{ old('status', $event->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="open" {{ old('status', $event->status) == 'open' ? 'selected' : '' }}>Open for Applications</option>
                                <option value="closed" {{ old('status', $event->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                                <option value="completed" {{ old('status', $event->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            @error('status')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Save as Template -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_template" value="1" {{ old('is_template', $event->is_template) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Save as template for future use</span>
                            </label>
                        </div>

                        @if($event->is_recurring)
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded">
                            <p class="text-sm text-blue-800">
                                <strong>Note:</strong> This is a recurring event. Changes will only affect this event, not the recurring instances.
                            </p>
                        </div>
                        @endif

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('admin.events.show', $event) }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Update Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Template auto-fill
        document.getElementById('template_id')?.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                document.getElementById('title').value = option.dataset.title || '';

                // Only update time fields if they're not disabled
                const startTimeField = document.getElementById('start_time');
                const endTimeField = document.getElementById('end_time');
                const slotDurationField = document.getElementById('slot_duration');

                if (!startTimeField.disabled) {
                    startTimeField.value = option.dataset.startTime?.substring(0, 5) || '';
                }
                if (!endTimeField.disabled) {
                    endTimeField.value = option.dataset.endTime?.substring(0, 5) || '';
                }
                if (!slotDurationField.disabled) {
                    slotDurationField.value = option.dataset.slotDuration || '';
                }

                document.getElementById('location').value = option.dataset.location || '';
                document.getElementById('notes').value = option.dataset.notes || '';
            }
        });
    </script>
</x-app-layout>
