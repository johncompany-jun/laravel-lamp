<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $event->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Event Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Event Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 font-medium">Date</p>
                            <p class="text-gray-900">{{ $event->event_date->format('M d, Y (D)') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-medium">Time</p>
                            <p class="text-gray-900">{{ date('H:i', strtotime($event->start_time)) }} - {{ date('H:i', strtotime($event->end_time)) }}</p>
                        </div>
                        @if($event->location)
                        <div>
                            <p class="text-sm text-gray-600 font-medium">Location</p>
                            <p class="text-gray-900">{{ $event->location }}</p>
                        </div>
                        @endif
                    </div>

                    @if($event->notes)
                    <div class="mt-4">
                        <p class="text-sm text-gray-600 font-medium">Notes</p>
                        <p class="text-gray-900 mt-1">{{ $event->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Application Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Apply to This Event</h3>

                    @if($existingApplications->count() > 0)
                        <div class="mb-6 bg-blue-50 border border-blue-200 p-4 rounded">
                            <p class="text-sm text-blue-800 mb-2">
                                <strong>You have already applied to this event.</strong> You can update your application by submitting the form again.
                            </p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('events.apply', $event) }}" id="applicationForm">
                        @csrf

                        <!-- Time Slot Selection with Availability -->
                        <div class="mb-6">
                            <label class="block font-medium text-sm text-gray-700 mb-3">
                                Select Time Slots and Your Availability
                            </label>
                            <p class="text-sm text-gray-600 mb-3">You can select multiple time slots. For each slot, indicate whether you are available or unavailable.</p>

                            @if($event->applicationSlots->count() > 0)
                                <div class="space-y-3">
                                    @foreach($event->applicationSlots as $slot)
                                        @php
                                            $existingApp = $existingApplications->get($slot->id);
                                            $isChecked = $existingApp !== null;
                                            $availability = $existingApp ? $existingApp->availability : 'available';
                                        @endphp
                                        <div class="border rounded-lg p-4" data-slot-container>
                                            <div class="flex items-start">
                                                <input type="checkbox"
                                                    name="slots[{{ $slot->id }}][selected]"
                                                    id="slot_{{ $slot->id }}"
                                                    value="1"
                                                    {{ $isChecked ? 'checked' : '' }}
                                                    class="slot-checkbox mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                    onchange="toggleSlotOptions({{ $slot->id }})">
                                                <input type="hidden" name="slots[{{ $slot->id }}][slot_id]" value="{{ $slot->id }}">

                                                <div class="ml-3 flex-1">
                                                    <label for="slot_{{ $slot->id }}" class="font-medium text-gray-900 cursor-pointer">
                                                        {{ date('H:i', strtotime($slot->start_time)) }} - {{ date('H:i', strtotime($slot->end_time)) }}
                                                        <span class="text-gray-500 text-sm ml-2">
                                                            ({{ $slot->applications->count() }} applications)
                                                        </span>
                                                    </label>

                                                    <div id="availability_{{ $slot->id }}" class="mt-2 ml-4 space-y-2" style="{{ $isChecked ? '' : 'display: none;' }}">
                                                        <label class="flex items-center">
                                                            <input type="radio"
                                                                name="slots[{{ $slot->id }}][availability]"
                                                                value="available"
                                                                {{ $availability === 'available' ? 'checked' : '' }}
                                                                class="rounded-full border-gray-300 text-green-600 shadow-sm focus:ring-green-500">
                                                            <span class="ml-2 text-sm text-gray-700">Available</span>
                                                        </label>
                                                        <label class="flex items-center">
                                                            <input type="radio"
                                                                name="slots[{{ $slot->id }}][availability]"
                                                                value="unavailable"
                                                                {{ $availability === 'unavailable' ? 'checked' : '' }}
                                                                class="rounded-full border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                                            <span class="ml-2 text-sm text-gray-700">Unavailable</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500">No time slots available for this event.</p>
                            @endif
                        </div>

                        <!-- Cart Transport Options -->
                        <div class="mb-6">
                            <label class="block font-medium text-sm text-gray-700 mb-3">
                                Cart Transport Support (Optional)
                            </label>

                            <div class="space-y-2">
                                <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50">
                                    <input type="checkbox"
                                        name="can_help_setup"
                                        value="1"
                                        {{ $existingApplications->first()?->can_help_setup ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-3 text-sm text-gray-900">I can help with setup (cart transport before the event)</span>
                                </label>

                                <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50">
                                    <input type="checkbox"
                                        name="can_help_cleanup"
                                        value="1"
                                        {{ $existingApplications->first()?->can_help_cleanup ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-3 text-sm text-gray-900">I can help with cleanup (cart transport after the event)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Comment -->
                        <div class="mb-6">
                            <label for="comment" class="block font-medium text-sm text-gray-700">
                                Comment (Optional)
                            </label>
                            <textarea name="comment" id="comment" rows="3" maxlength="500"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                placeholder="Any additional notes or comments...">{{ old('comment', $existingApplications->first()?->comment) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
                        </div>

                        <div class="mt-8 pt-6 border-t-2 border-gray-300">
                            <div class="flex items-center justify-between gap-4">
                                <a href="{{ route('events.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                    ← Back to Events
                                </a>
                                <button type="submit" style="background-color: #4F46E5 !important; color: white !important; padding: 12px 24px !important; font-size: 16px !important; font-weight: bold !important; border-radius: 8px !important;">
                                    SUBMIT APPLICATION
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSlotOptions(slotId) {
            const checkbox = document.getElementById('slot_' + slotId);
            const availabilityDiv = document.getElementById('availability_' + slotId);

            if (checkbox.checked) {
                availabilityDiv.style.display = 'block';
                // Set default to available if nothing is selected
                const radios = availabilityDiv.querySelectorAll('input[type="radio"]');
                const hasChecked = Array.from(radios).some(r => r.checked);
                if (!hasChecked) {
                    radios[0].checked = true; // Select "available" by default
                }
            } else {
                availabilityDiv.style.display = 'none';
            }
        }

        // Form validation and data preparation
        document.getElementById('applicationForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const checkboxes = document.querySelectorAll('.slot-checkbox:checked');

            if (checkboxes.length === 0) {
                alert('Please select at least one time slot.');
                return false;
            }

            // Validate that each checked slot has an availability selected
            let valid = true;
            checkboxes.forEach(checkbox => {
                const slotId = checkbox.id.replace('slot_', '');
                const availabilityRadios = document.querySelectorAll(`input[name="slots[${slotId}][availability]"]:checked`);
                if (availabilityRadios.length === 0) {
                    valid = false;
                }
            });

            if (!valid) {
                alert('Please select availability for all selected time slots.');
                return false;
            }

            // Remove unchecked slots from form data before submission
            const allSlotCheckboxes = document.querySelectorAll('.slot-checkbox');
            allSlotCheckboxes.forEach(checkbox => {
                const slotId = checkbox.id.replace('slot_', '');
                if (!checkbox.checked) {
                    // Disable inputs for unchecked slots so they won't be submitted
                    const slotInputs = document.querySelectorAll(`input[name^="slots[${slotId}]"]`);
                    slotInputs.forEach(input => {
                        input.disabled = true;
                    });
                }
            });

            // Submit the form
            this.submit();
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.slot-checkbox').forEach(checkbox => {
                const slotId = checkbox.id.replace('slot_', '');
                toggleSlotOptions(slotId);
            });
        });
    </script>
</x-app-layout>
