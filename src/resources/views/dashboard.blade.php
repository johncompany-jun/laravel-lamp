<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('dashboard.dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- My Assignments -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('dashboard.my_confirmed_schedule') }}</h3>

                    @if($myAssignments->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('dashboard.event') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('dashboard.date') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('dashboard.time_slot') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('dashboard.location') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($myAssignments as $assignment)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <a href="{{ route('events.assignments.view', $assignment->event) }}" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                                    {{ $assignment->event->title }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $assignment->event->event_date->format('Y-m-d (D)') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ date('H:i', strtotime($assignment->slot->start_time)) }} - {{ date('H:i', strtotime($assignment->slot->end_time)) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $assignment->slot->location ?? __('dashboard.n_a') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">{{ __('dashboard.no_assignments_yet') }}</p>
                    @endif
                </div>
            </div>

            <!-- My Applications -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('dashboard.my_applications') }}</h3>

                    @if($myApplications->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10">{{ __('dashboard.event') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('dashboard.date') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('dashboard.time_slot') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('dashboard.applied') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($myApplications as $applicationGroup)
                                        @php
                                            $event = $applicationGroup['event'];
                                            $applications = $applicationGroup['applications'];
                                            $appliedAt = $applicationGroup['applied_at'];
                                            $applicationSlots = $event->applicationSlots->sortBy('start_time');
                                            // カート運搬サポートの情報を取得（全申込から最初のものを使用）
                                            $firstApp = $applications->first();
                                            $canHelpSetup = $firstApp->can_help_setup ?? false;
                                            $canHelpCleanup = $firstApp->can_help_cleanup ?? false;
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-0 bg-white z-10">
                                                <a href="{{ route('events.show', $event) }}" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                                    {{ $event->title }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $event->event_date->format('Y-m-d') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                <div class="flex flex-wrap gap-2 mb-2">
                                                    @foreach($applicationSlots as $slot)
                                                        @php
                                                            $app = $applications->get($slot->id);
                                                        @endphp
                                                        @if($app)
                                                            <div class="whitespace-nowrap">
                                                                <div class="text-xs text-gray-600 mb-1">
                                                                    {{ date('H:i', strtotime($slot->start_time)) }}-{{ date('H:i', strtotime($slot->end_time)) }}
                                                                </div>
                                                                @if($app->availability === 'available')
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                        {{ __('dashboard.available') }}
                                                                    </span>
                                                                @else
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                                        {{ __('dashboard.unavailable') }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                                @if($canHelpSetup || $canHelpCleanup)
                                                    <div class="flex flex-wrap gap-1 mt-2">
                                                        @if($canHelpSetup)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                                {{ __('dashboard.setup') }}
                                                            </span>
                                                        @endif
                                                        @if($canHelpCleanup)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                                {{ __('dashboard.cleanup') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $appliedAt->format('Y-m-d') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">{{ __('dashboard.no_applications_yet') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
