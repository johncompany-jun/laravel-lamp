<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('events.create_event') }}
        </h2>
    </x-slot>
    <!-- Cache Buster: {{ now() }} -->

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.events.store') }}" id="eventForm">
                        @csrf

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
                                        data-slot-duration="{{ $template->slot_duration }}"
                                        data-location="{{ $template->location }}"
                                        data-notes="{{ $template->notes }}">
                                        {{ $template->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Title and Status -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="title" class="block font-medium text-sm text-gray-700">{{ __('events.title') }}</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" required
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
                                    id="status"
                                    :selected="old('status', 'draft')"
                                    required
                                    class="mt-1 block w-full"
                                />
                                @error('status')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Event Date and Time Range -->
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="event_date" class="block font-medium text-sm text-gray-700">{{ __('events.event_date') }}</label>
                                <input type="date" name="event_date" id="event_date" value="{{ old('event_date') }}" required
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                @error('event_date')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="start_time" class="block font-medium text-sm text-gray-700">{{ __('events.start_time') }}</label>
                                <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" required
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                @error('start_time')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="end_time" class="block font-medium text-sm text-gray-700">{{ __('events.end_time') }}</label>
                                <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" required
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                @error('end_time')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Application Slot, Assignment Slot, Location -->
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="application_slot_duration" class="block font-medium text-sm text-gray-700">{{ __('events.application_slot_duration') }}</label>
                                <x-enum-select
                                    :enum="\App\Enums\ApplicationSlotDuration::class"
                                    name="application_slot_duration"
                                    id="application_slot_duration"
                                    :selected="old('application_slot_duration', 60)"
                                    required
                                    class="mt-1 block w-full"
                                />
                                <p class="text-xs text-gray-500 mt-1">{{ __('events.application_slot_duration_help') }}</p>
                                @error('application_slot_duration')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="slot_duration" class="block font-medium text-sm text-gray-700">{{ __('events.assignment_slot_duration') }}</label>
                                <x-enum-select
                                    :enum="\App\Enums\AssignmentSlotDuration::class"
                                    name="slot_duration"
                                    id="slot_duration"
                                    :selected="old('slot_duration', 20)"
                                    required
                                    class="mt-1 block w-full"
                                />
                                <p class="text-xs text-gray-500 mt-1">{{ __('events.assignment_slot_duration_help') }}</p>
                                @error('slot_duration')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="location" class="block font-medium text-sm text-gray-700">{{ __('events.location') }}</label>
                                <input type="text" name="location" id="location" value="{{ old('location') }}"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                @error('location')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Multiple Locations for Assignment -->
                        <div class="mb-4">
                            <label class="block font-medium text-sm text-gray-700 mb-2">{{ __('events.locations_for_assignment') }}</label>
                            <div id="locationsContainer" class="grid grid-cols-3 gap-4">
                                <input type="text" name="locations[]" placeholder="例: 北西" value="{{ old('locations.0') }}"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                <input type="text" name="locations[]" placeholder="例: 北東" value="{{ old('locations.1') }}"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                                <input type="text" name="locations[]" placeholder="その他" value="{{ old('locations.2') }}"
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
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Options -->
                        <div class="flex gap-6 mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">{{ __('events.repeat_weekly') }}</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_template" value="1" {{ old('is_template') ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">{{ __('events.save_as_template') }}</span>
                            </label>
                        </div>

                        <!-- Recurrence End Date (shown when is_recurring is checked) -->
                        <div id="recurrence_fields" class="mb-4" style="display: none;">
                            <input type="hidden" name="recurrence_type" value="weekly">
                            <label for="recurrence_end_date" class="block font-medium text-sm text-gray-700">{{ __('events.repeat_until') }}</label>
                            <input type="date" name="recurrence_end_date" id="recurrence_end_date" value="{{ old('recurrence_end_date') }}"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-1/2">
                            @error('recurrence_end_date')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('admin.events.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('events.cancel') }}</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                {{ __('events.create_event_button') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('admin.events.traits.event-form-scripts')
</x-app-layout>
