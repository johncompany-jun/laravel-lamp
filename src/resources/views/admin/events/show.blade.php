<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('events.event_details') }}
            </h2>
            <div class="flex gap-3">
                <a href="{{ route('admin.events.edit', $event) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    {{ __('events.edit_event_btn') }}
                </a>
                <a href="{{ route('admin.events.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('events.back_to_list') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Event Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('events.event_information') }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('events.title') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $event->title }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('events.date') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $event->event_date->format('Y-m-d (l)') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('events.time') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ date('H:i', strtotime($event->start_time)) }} - {{ date('H:i', strtotime($event->end_time)) }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('events.slot_duration') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $event->slot_duration->translatedLabel() }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('events.location') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $event->location ?? __('events.not_set') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('events.status') }}</label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      style="background-color: {{ $event->status->badgeConfig()['bg'] }}; color: {{ $event->status->badgeConfig()['color'] }};">
                                    {{ $event->status->translatedLabel() }}
                                </span>
                            </p>
                        </div>

                        @if($event->is_recurring)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">{{ __('events.recurrence') }}</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ \Carbon\Carbon::parse($event->recurrence_end_date)->format('Y-m-d') }}{{ __('events.weekly_until') }}
                                </span>
                                @if($event->childEvents->count() > 0)
                                    <span class="ml-2 text-gray-600">({{ $event->childEvents->count() }}{{ __('events.recurring_instances') }})</span>
                                @endif
                            </p>
                        </div>
                        @endif

                        @if($event->is_template)
                        <div class="md:col-span-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                {{ __('events.template_event') }}
                            </span>
                        </div>
                        @endif

                        @if($event->notes)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">{{ __('events.notes') }}</label>
                            <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $event->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Time Slots -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('events.time_slots') }} ({{ $event->slots->count() }})</h3>

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
                                                {{ __('events.capacity') }}: {{ $slot->assignments->count() }} / {{ $slot->capacity }}
                                            </p>
                                        </div>
                                        @if($slot->isFull())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                {{ __('events.full') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                {{ __('events.available') }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($slot->assignments->count() > 0)
                                        <div class="mt-3 pt-3 border-t">
                                            <p class="text-xs font-medium text-gray-700 mb-1">{{ __('events.assigned') }}:</p>
                                            @foreach($slot->assignments as $assignment)
                                                <p class="text-sm text-gray-600">{{ $assignment->user->name }}</p>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">{{ __('events.no_time_slots') }}</p>
                    @endif
                </div>
            </div>

            <!-- Applications -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('events.applications') }} ({{ $event->applications->count() }})</h3>

                    @if($event->applications->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('events.user') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('events.availability') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('events.comment') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('events.applied_at') }}</th>
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
                                                        {{ __('events.available_status') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        {{ __('events.unavailable_status') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ $application->comment ?? __('events.not_set') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $application->created_at->format('Y-m-d H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">{{ __('events.no_applications') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
