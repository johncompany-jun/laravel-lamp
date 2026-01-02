<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('events.edit_event') }}
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
                            <strong>{{ __('Note') }}:</strong> {{ __('events.note_assigned_slots') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.events.update', $event) }}" id="eventForm">
                        @csrf
                        @method('PUT')

                        <!-- Template Selection -->
                        @if($templates->count() > 0)
                        <div class="mb-6">
                            <label for="template_id" class="block font-medium text-sm text-gray-700">{{ __('events.use_template') }}</label>
                            <select id="template_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                <option value="">{{ __('events.select_template') }}</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}"
                                        data-title="{{ $template->title }}"
                                        data-start-time="{{ $template->start_time }}"
                                        data-end-time="{{ $template->end_time }}"
                                        data-slot-duration="{{ $template->slot_duration?->value ?? '' }}"
                                        data-application-slot-duration="{{ $template->application_slot_duration?->value ?? '' }}"
                                        data-location="{{ $template->location ?? '' }}"
                                        data-notes="{{ $template->notes ?? '' }}">
                                        {{ $template->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Title and Status (2-column) -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="title" class="block font-medium text-sm text-gray-700">{{ __('events.title') }}</label>
                                <input type="text" name="title" id="title" value="{{ old('title', $event->title) }}" required
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                @error('title')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="status" class="block font-medium text-sm text-gray-700">{{ __('events.status') }}</label>
                                <x-enum-select
                                    :enum="\App\Enums\EventStatus::class"
                                    name="status"
                                    :selected="old('status', $event->status->value)"
                                    required
                                    class="mt-1 block w-full"
                                />
                                @error('status')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Event Date, Start Time, End Time (3-column) -->
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="event_date" class="block font-medium text-sm text-gray-700">{{ __('events.event_date') }}</label>
                                <input type="date" name="event_date" id="event_date" value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" required
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                @error('event_date')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="start_time" class="block font-medium text-sm text-gray-700">{{ __('events.start_time') }}</label>
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
                                <label for="end_time" class="block font-medium text-sm text-gray-700">{{ __('events.end_time') }}</label>
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

                        <!-- Application Slot, Assignment Slot, Location (3-column) -->
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="application_slot_duration" class="block font-medium text-sm text-gray-700">{{ __('events.application_slot_duration') }}</label>
                                <x-enum-select
                                    :enum="\App\Enums\ApplicationSlotDuration::class"
                                    name="application_slot_duration"
                                    :selected="old('application_slot_duration', $event->application_slot_duration->value)"
                                    required
                                    :disabled="$event->applicationSlots()->whereHas('applications')->exists()"
                                    class="mt-1 block w-full"
                                />
                                <p class="text-xs text-gray-500 mt-1">{{ __('events.application_slot_duration_help') }}</p>
                                @if($event->applicationSlots()->whereHas('applications')->exists())
                                    <input type="hidden" name="application_slot_duration" value="{{ $event->application_slot_duration->value }}">
                                @endif
                                @error('application_slot_duration')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="slot_duration" class="block font-medium text-sm text-gray-700">{{ __('events.assignment_slot_duration') }}</label>
                                <x-enum-select
                                    :enum="\App\Enums\AssignmentSlotDuration::class"
                                    name="slot_duration"
                                    :selected="old('slot_duration', $event->slot_duration->value)"
                                    required
                                    :disabled="$event->slots()->whereHas('assignments')->exists()"
                                    class="mt-1 block w-full"
                                />
                                <p class="text-xs text-gray-500 mt-1">{{ __('events.assignment_slot_duration_help') }}</p>
                                @if($event->slots()->whereHas('assignments')->exists())
                                    <input type="hidden" name="slot_duration" value="{{ $event->slot_duration->value }}">
                                @endif
                                @error('slot_duration')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="location" class="block font-medium text-sm text-gray-700">{{ __('events.location') }}</label>
                                <input type="text" name="location" id="location" value="{{ old('location', $event->location) }}"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                @error('location')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Multiple Locations for Assignment (3-column) -->
                        <div class="mb-4">
                            <label class="block font-medium text-sm text-gray-700 mb-2">{{ __('events.locations_for_assignment') }}</label>
                            <div class="grid grid-cols-3 gap-4">
                                @php
                                    $existingLocations = old('locations', $event->locations ?? []);
                                    $locationInputs = array_pad($existingLocations, 3, '');
                                @endphp
                                <input type="text" name="locations[]" placeholder="例: 北西" value="{{ $locationInputs[0] }}"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                <input type="text" name="locations[]" placeholder="例: 北東" value="{{ $locationInputs[1] }}"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                <input type="text" name="locations[]" placeholder="例: 南側 (任意)" value="{{ $locationInputs[2] }}"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ __('events.locations_help') }}</p>
                            @error('locations')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="block font-medium text-sm text-gray-700">{{ __('events.notes') }}</label>
                            <textarea name="notes" id="notes" rows="4"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">{{ old('notes', $event->notes) }}</textarea>
                            @error('notes')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Save as Template -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_template" value="1" {{ old('is_template', $event->is_template) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">{{ __('events.save_as_template') }}</span>
                            </label>
                        </div>

                        @if($event->is_recurring)
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded">
                            <p class="text-sm text-blue-800">
                                <strong>{{ __('Note') }}:</strong> {{ __('events.note_recurring_event') }}
                            </p>
                        </div>
                        @endif

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('admin.events.show', $event) }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('events.cancel') }}</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                {{ __('events.update_event') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('admin.events.traits.event-edit-form-scripts')
</x-app-layout>
