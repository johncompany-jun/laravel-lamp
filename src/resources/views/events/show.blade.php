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
                    <h3 class="text-lg font-semibold mb-4">{{ __('events.event_information') }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 font-medium">{{ __('events.date') }}</p>
                            <p class="text-gray-900">{{ $event->event_date->format('Y-m-d (D)') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-medium">{{ __('events.time') }}</p>
                            <p class="text-gray-900">{{ date('H:i', strtotime($event->start_time)) }} - {{ date('H:i', strtotime($event->end_time)) }}</p>
                        </div>
                        @if($event->location)
                        <div>
                            <p class="text-sm text-gray-600 font-medium">{{ __('events.location') }}</p>
                            <p class="text-gray-900">{{ $event->location }}</p>
                        </div>
                        @endif
                    </div>

                    @if($event->notes)
                    <div class="mt-4">
                        <p class="text-sm text-gray-600 font-medium">{{ __('events.notes') }}</p>
                        <p class="text-gray-900 mt-1 whitespace-pre-line">{{ $event->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Application Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('events.apply_to_event') }}</h3>

                    @if($existingApplications->count() > 0)
                        <div class="mb-6 bg-blue-50 border border-blue-200 p-4 rounded">
                            <p class="text-sm text-blue-800 mb-2">
                                <strong>{{ __('events.already_applied') }}</strong> {{ __('events.can_update_application') }}
                            </p>

                            @php
                                $firstApp = $existingApplications->first();
                            @endphp

                            @if($firstApp && $firstApp->comment)
                                <div class="mt-3 pt-3 border-t border-blue-300">
                                    <p class="text-xs text-blue-700 font-medium mb-1">{{ __('events.comment') }}:</p>
                                    <div class="text-sm text-blue-900 whitespace-pre-line bg-white p-2 rounded">{{ $firstApp->comment }}</div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <form method="POST" action="{{ route('events.apply', $event) }}" id="applicationForm">
                        @csrf

                        <!-- Time Slot Selection with Availability -->
                        <div class="mb-6">
                            <label class="block font-medium text-sm text-gray-700 mb-3">
                                {{ __('events.select_time_slots') }}
                            </label>
                            <p class="text-sm text-gray-600 mb-3">{{ __('events.select_availability_for_all_slots') }}</p>

                            @if($event->applicationSlots->count() > 0)
                                <div class="space-y-3">
                                    @foreach($event->applicationSlots as $slot)
                                        @php
                                            $existingApp = $existingApplications->get($slot->id);
                                            $availability = $existingApp ? $existingApp->availability : 'available';
                                        @endphp
                                        <div class="border rounded-lg p-4">
                                            <div class="mb-2">
                                                <label class="font-medium text-gray-900">
                                                    {{ date('H:i', strtotime($slot->start_time)) }} - {{ date('H:i', strtotime($slot->end_time)) }}
                                                    <span class="text-gray-500 text-sm ml-2">
                                                        ({{ $slot->applications->count() }} {{ __('events.applications_count') }})
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="flex gap-4 ml-4">
                                                <input type="hidden" name="slots[{{ $slot->id }}][slot_id]" value="{{ $slot->id }}">
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="radio"
                                                        name="slots[{{ $slot->id }}][availability]"
                                                        value="available"
                                                        {{ $availability === 'available' ? 'checked' : '' }}
                                                        class="rounded-full border-gray-300 text-green-600 shadow-sm focus:ring-green-500"
                                                        required>
                                                    <span class="ml-2 text-sm text-gray-700">{{ __('events.available_status') }}</span>
                                                </label>
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="radio"
                                                        name="slots[{{ $slot->id }}][availability]"
                                                        value="unavailable"
                                                        {{ $availability === 'unavailable' ? 'checked' : '' }}
                                                        class="rounded-full border-gray-300 text-red-600 shadow-sm focus:ring-red-500"
                                                        required>
                                                    <span class="ml-2 text-sm text-gray-700">{{ __('events.unavailable_status') }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500">{{ __('events.no_time_slots') }}</p>
                            @endif
                        </div>

                        <!-- Cart Transport Options -->
                        <div class="mb-6">
                            <label class="block font-medium text-sm text-gray-700 mb-3">
                                {{ __('events.cart_transport_support') }}
                            </label>

                            <div class="space-y-2">
                                <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50">
                                    <input type="checkbox"
                                        name="can_help_setup"
                                        value="1"
                                        {{ $existingApplications->first()?->can_help_setup ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-3 text-sm text-gray-900">{{ __('events.can_help_setup') }}</span>
                                </label>

                                <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50">
                                    <input type="checkbox"
                                        name="can_help_cleanup"
                                        value="1"
                                        {{ $existingApplications->first()?->can_help_cleanup ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-3 text-sm text-gray-900">{{ __('events.can_help_cleanup') }}</span>
                                </label>

                                <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50">
                                    <input type="checkbox"
                                        name="can_transport_by_car"
                                        value="1"
                                        {{ $existingApplications->first()?->can_transport_by_car ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-3 text-sm text-gray-900">{{ __('events.can_transport_by_car') }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Comment -->
                        <div class="mb-6">
                            <label for="comment" class="block font-medium text-sm text-gray-700">
                                {{ __('events.comment_optional') }}
                            </label>
                            <textarea name="comment" id="comment" rows="3" maxlength="500"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                placeholder="{{ __('events.comment_placeholder') }}">{{ old('comment', $existingApplications->first()?->comment) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">{{ __('events.max_characters') }}</p>
                        </div>

                        <div class="mt-8 pt-6 border-t-2 border-gray-300">
                            <div class="flex items-center justify-between gap-4">
                                <a href="{{ route('events.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                    {{ __('events.back_to_events') }}
                                </a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('events.submit_application') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // No additional JavaScript needed - HTML5 validation handles required fields
    </script>
</x-app-layout>
