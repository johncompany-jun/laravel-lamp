<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.events.store') }}" id="eventForm">
                        @csrf

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
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                            @error('title')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Event Date -->
                        <div class="mb-4">
                            <label for="event_date" class="block font-medium text-sm text-gray-700">Event Date</label>
                            <input type="date" name="event_date" id="event_date" value="{{ old('event_date') }}" required
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                            @error('event_date')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Time Range -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="start_time" class="block font-medium text-sm text-gray-700">Start Time</label>
                                <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" required
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                @error('start_time')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="end_time" class="block font-medium text-sm text-gray-700">End Time</label>
                                <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" required
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                @error('end_time')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Slot Duration -->
                        <div class="mb-4">
                            <label for="slot_duration" class="block font-medium text-sm text-gray-700">Assignment Slot Duration (minutes)</label>
                            <select name="slot_duration" id="slot_duration" required
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                <option value="10" {{ old('slot_duration') == 10 ? 'selected' : '' }}>10 minutes</option>
                                <option value="20" {{ old('slot_duration') == 20 ? 'selected' : '' }}>20 minutes</option>
                                <option value="30" {{ old('slot_duration') == 30 ? 'selected' : '' }}>30 minutes</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Duration for admin to assign users to time slots</p>
                            @error('slot_duration')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Application Slot Duration -->
                        <div class="mb-4">
                            <label for="application_slot_duration" class="block font-medium text-sm text-gray-700">Application Slot Duration (minutes)</label>
                            <select name="application_slot_duration" id="application_slot_duration" required
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                <option value="30" {{ old('application_slot_duration') == 30 ? 'selected' : '' }}>30 minutes</option>
                                <option value="60" {{ old('application_slot_duration') == 60 ? 'selected' : '' }}>1 hour</option>
                                <option value="90" {{ old('application_slot_duration') == 90 ? 'selected' : '' }}>1.5 hours</option>
                                <option value="120" {{ old('application_slot_duration') == 120 ? 'selected' : '' }}>2 hours</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Duration for users to apply for availability</p>
                            @error('application_slot_duration')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div class="mb-4">
                            <label for="location" class="block font-medium text-sm text-gray-700">Location</label>
                            <input type="text" name="location" id="location" value="{{ old('location') }}"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                            @error('location')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Multiple Locations -->
                        <div class="mb-4">
                            <label class="block font-medium text-sm text-gray-700 mb-2">Locations for Assignment (2-3 areas)</label>
                            <div id="locationsContainer">
                                <div class="flex gap-2 mb-2">
                                    <input type="text" name="locations[]" placeholder="例: 北西" value="{{ old('locations.0') }}"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                </div>
                                <div class="flex gap-2 mb-2">
                                    <input type="text" name="locations[]" placeholder="例: 北東" value="{{ old('locations.1') }}"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                </div>
                                <div class="flex gap-2 mb-2">
                                    <input type="text" name="locations[]" placeholder="例: 南側 (optional)" value="{{ old('locations.2') }}"
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
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label for="status" class="block font-medium text-sm text-gray-700">Status</label>
                            <select name="status" id="status" required
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="open" {{ old('status') == 'open' ? 'selected' : '' }}>Open for Applications</option>
                                <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            @error('status')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Recurring Event -->
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Repeat Weekly</span>
                            </label>
                        </div>

                        <!-- Recurrence End Date (shown when is_recurring is checked) -->
                        <div id="recurrence_fields" class="mb-4" style="display: none;">
                            <input type="hidden" name="recurrence_type" value="weekly">
                            <label for="recurrence_end_date" class="block font-medium text-sm text-gray-700">Repeat Until</label>
                            <input type="date" name="recurrence_end_date" id="recurrence_end_date" value="{{ old('recurrence_end_date') }}"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                            @error('recurrence_end_date')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Save as Template -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_template" value="1" {{ old('is_template') ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Save as template for future use</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('admin.events.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Create Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle recurrence fields
        document.getElementById('is_recurring').addEventListener('change', function() {
            const recurrenceFields = document.getElementById('recurrence_fields');
            recurrenceFields.style.display = this.checked ? 'block' : 'none';
        });

        // Show recurrence fields if already checked (validation errors)
        if (document.getElementById('is_recurring').checked) {
            document.getElementById('recurrence_fields').style.display = 'block';
        }

        // Template auto-fill
        document.getElementById('template_id')?.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                document.getElementById('title').value = option.dataset.title || '';
                document.getElementById('start_time').value = option.dataset.startTime?.substring(0, 5) || '';
                document.getElementById('end_time').value = option.dataset.endTime?.substring(0, 5) || '';
                document.getElementById('slot_duration').value = option.dataset.slotDuration || '';
                document.getElementById('location').value = option.dataset.location || '';
                document.getElementById('notes').value = option.dataset.notes || '';
            }
        });
    </script>
</x-app-layout>
