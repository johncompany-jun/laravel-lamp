<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $event->title }} - アサイン表
            </h2>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                ダッシュボードに戻る
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Event Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">{{ __('events.event_details') }}</h3>
                        @if($event->status === App\Enums\EventStatus::COMPLETED)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                {{ __('dashboard.event_cancelled') }}
                            </span>
                        @endif
                    </div>
                    <div class="grid grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('events.date') }}</p>
                            <p class="font-medium">{{ $event->event_date->format('Y-m-d (D)') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('events.time') }}</p>
                            <p class="font-medium">{{ date('H:i', strtotime($event->start_time)) }} - {{ date('H:i', strtotime($event->end_time)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('events.location') }}</p>
                            <p class="font-medium">{{ $event->location ?? __('events.not_set') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('events.locations') }}</p>
                            <p class="font-medium">{{ !empty($event->locations) ? implode(', ', $event->locations) : __('events.not_set') }}</p>
                        </div>
                    </div>
                    @if($event->status === App\Enums\EventStatus::COMPLETED)
                        <div class="mt-4 p-3 bg-red-50 border-l-4 border-red-500 rounded">
                            <p class="text-red-700 font-bold text-lg">このPWイベントは中止になりました</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Assignment Grid (Read-only) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">アサイン一覧</h3>

                    @php
                        // Get unique time slots (sorted)
                        $timeSlots = $event->slots->unique(function ($slot) {
                            return $slot->start_time . '-' . $slot->end_time;
                        })->sortBy('start_time')->values();
                    @endphp

                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse border border-gray-300">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-300 px-4 py-2 text-left font-semibold">{{ __('events.time') }}</th>
                                    @foreach($locations as $location)
                                        <th class="border border-gray-300 px-4 py-2 text-center font-semibold">{{ $location }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timeSlots as $timeSlot)
                                    @php
                                        $timeKey = date('H:i', strtotime($timeSlot->start_time)) . '-' . date('H:i', strtotime($timeSlot->end_time));
                                        $timeLabel = date('H:i', strtotime($timeSlot->start_time)) . ' - ' . date('H:i', strtotime($timeSlot->end_time));
                                    @endphp
                                    <tr>
                                        <td class="border border-gray-300 px-4 py-2 font-medium bg-gray-50">
                                            {{ $timeLabel }}
                                        </td>
                                        @foreach($locations as $location)
                                            @php
                                                $slot = $slotMatrix[$timeKey][$location] ?? null;
                                                $slotAssignments = $slot ? $slot->assignments : collect();
                                                $participants = $slotAssignments->where('role', 'participant');
                                                $leader = $slotAssignments->where('role', 'leader')->first();
                                            @endphp
                                            <td class="border border-gray-300 px-4 py-3 align-top">
                                                @if($slot)
                                                    <div class="space-y-2">
                                                        @if($participants->count() > 0)
                                                            <div>
                                                                <div class="text-xs text-gray-500 mb-1">参加者:</div>
                                                                @foreach($participants as $assignment)
                                                                    <div class="inline-block px-2 py-1 bg-green-100 text-green-800 rounded text-sm mr-1 mb-1">
                                                                        👤 {{ $assignment->user->name }}
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @if($leader)
                                                            <div>
                                                                <div class="text-xs text-gray-500 mb-1">見守り:</div>
                                                                <div class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                                                                    👀 {{ $leader->user->name }}
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if($participants->count() === 0 && !$leader)
                                                            <span class="text-gray-400 text-sm">未アサイン</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 text-sm">{{ __('events.no_slot') }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
