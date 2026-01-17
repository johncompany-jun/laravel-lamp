<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('events.create_assignments_title') }}: {{ $event->title }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    {{ __('events.view_details') }}
                </a>
                <a href="{{ route('admin.events.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('events.back_to_list') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
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

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Event Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('events.event_details') }}</h3>
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
                </div>
            </div>

            <!-- Assignment Grid -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('events.assign_users_to_slots') }}</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('events.assignment_instruction') }}
                    </p>

                    <form method="POST" action="{{ route('admin.events.assignments.store', $event) }}" id="assignmentForm">
                        @csrf

                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full border-collapse border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold sticky left-0 bg-gray-100 z-10">{{ __('events.time') }}</th>
                                        @if(empty($locations))
                                            <th class="border border-gray-300 px-4 py-2 text-center font-semibold">
                                                {{ __('events.assignments') }}
                                            </th>
                                        @else
                                            @foreach($locations as $location)
                                                <th class="border border-gray-300 px-4 py-2 text-center font-semibold">
                                                    {{ $location }}
                                                    <div class="text-xs font-normal text-gray-500 mt-1">
                                                        {{ __('events.participants_and_leader') }}
                                                    </div>
                                                </th>
                                            @endforeach
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($timeSlots as $timeSlot)
                                        @php
                                            $timeKey = $timeSlot->start_time . '-' . $timeSlot->end_time;
                                            $startTime = date('H:i', strtotime($timeSlot->start_time));
                                            $endTime = date('H:i', strtotime($timeSlot->end_time));
                                        @endphp
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2 font-medium sticky left-0 bg-white z-10">
                                                {{ $startTime }}<br>
                                                <span class="text-xs text-gray-500">{{ $endTime }}</span>
                                            </td>
                                            @if(empty($locations))
                                                @php
                                                    $slot = $slotMatrix[$timeKey]['default'] ?? null;
                                                @endphp
                                                <td class="border border-gray-300 px-2 py-2 {{ $slot ? 'bg-gray-50 cursor-pointer hover:bg-gray-100' : 'bg-red-50' }}"
                                                    data-slot-id="{{ $slot?->id ?? '' }}"
                                                    data-time="{{ $startTime }}-{{$endTime}}"
                                                    data-location="default"
                                                    onclick="{{ $slot ? 'openAssignmentModal(this)' : 'alert(\'' . __('events.no_slot_found') . '\')' }}">
                                                    <div class="min-h-[60px] assignment-cell" id="cell-{{ $slot?->id ?? 'none' }}">
                                                        @if($slot)
                                                            <span class="text-gray-400 text-sm">{{ __('events.click_to_assign') }}</span>
                                                        @else
                                                            <span class="text-red-500 text-xs">{{ __('events.no_slot') }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                            @else
                                                @foreach($locations as $location)
                                                    @php
                                                        $slot = $slotMatrix[$timeKey][$location] ?? null;
                                                    @endphp
                                                    <td class="border border-gray-300 px-2 py-2 {{ $slot ? 'bg-gray-50 cursor-pointer hover:bg-gray-100' : 'bg-red-50' }}"
                                                        data-slot-id="{{ $slot?->id ?? '' }}"
                                                        data-time="{{ $startTime }}-{{$endTime}}"
                                                        data-location="{{ $location }}"
                                                        onclick="{{ $slot ? 'openAssignmentModal(this)' : 'alert(\'' . $location . __('events.no_slot_for_location') . '\')' }}">
                                                        <div class="min-h-[60px] assignment-cell" id="cell-{{ $slot?->id ?? 'none-' . $loop->index }}">
                                                            @if($slot)
                                                                <span class="text-gray-400 text-sm">{{ __('events.click_to_assign') }}</span>
                                                            @else
                                                                <span class="text-red-500 text-xs">{{ __('events.no_slot') }}</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endforeach
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Special Role Assignments -->
                        <div class="mt-8 pt-8 border-t-2 border-gray-300">
                            <h4 class="text-lg font-semibold mb-4">準備と運搬</h4>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Setup Section -->
                                <div>
                                    <h5 class="text-md font-semibold mb-2 text-gray-700">準備 (2人)</h5>
                                    <div class="border border-gray-300 rounded-lg p-3 bg-gray-50 cursor-pointer hover:bg-gray-100"
                                         data-special-role="setup"
                                         onclick="openSpecialRoleModal(this)">
                                        <div class="min-h-[80px] special-assignment-cell" id="special-cell-setup">
                                            <span class="text-gray-400 text-sm">クリックして選択</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cleanup Section -->
                                <div>
                                    <h5 class="text-md font-semibold mb-2 text-gray-700">片付け (2人)</h5>
                                    <div class="border border-gray-300 rounded-lg p-3 bg-gray-50 cursor-pointer hover:bg-gray-100"
                                         data-special-role="cleanup"
                                         onclick="openSpecialRoleModal(this)">
                                        <div class="min-h-[80px] special-assignment-cell" id="special-cell-cleanup">
                                            <span class="text-gray-400 text-sm">クリックして選択</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transport First Half Section -->
                                <div>
                                    <h5 class="text-md font-semibold mb-2 text-gray-700">車運搬前半 (1人)</h5>
                                    <div class="border border-gray-300 rounded-lg p-3 bg-gray-50 cursor-pointer hover:bg-gray-100"
                                         data-special-role="transport_first"
                                         onclick="openSpecialRoleModal(this)">
                                        <div class="min-h-[80px] special-assignment-cell" id="special-cell-transport_first">
                                            <span class="text-gray-400 text-sm">クリックして選択</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transport Second Half Section -->
                                <div>
                                    <h5 class="text-md font-semibold mb-2 text-gray-700">車運搬後半 (1人)</h5>
                                    <div class="border border-gray-300 rounded-lg p-3 bg-gray-50 cursor-pointer hover:bg-gray-100"
                                         data-special-role="transport_second"
                                         onclick="openSpecialRoleModal(this)">
                                        <div class="min-h-[80px] special-assignment-cell" id="special-cell-transport_second">
                                            <span class="text-gray-400 text-sm">クリックして選択</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between items-center">
                            <div id="assignmentCount" class="text-sm text-gray-600">
                                <span class="font-semibold" id="assignedCount">0</span> {{ __('events.assignments_created') }}
                            </div>
                            <div style="display: flex; gap: 16px;">
                                <a href="{{ route('admin.events.index') }}" style="display: inline-block; padding: 8px 16px; background-color: #E5E7EB; color: #374151; border-radius: 6px; text-decoration: none; border: 1px solid #D1D5DB; font-size: 14px; font-weight: 500;">
                                    {{ __('events.cancel') }}
                                </a>
                                <button type="submit" style="display: inline-block; padding: 10px 24px; background-color: #4F46E5; color: white; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#4338CA'" onmouseout="this.style.backgroundColor='#4F46E5'">
                                    {{ __('events.save_assignments') }}
                                </button>
                            </div>
                        </div>

                        <!-- Hidden inputs for assignments -->
                        <div id="assignmentInputs">
                            <!-- This ensures assignments array is always sent, even if empty -->
                            <input type="hidden" name="assignments" value="">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Modal -->
    <div id="assignmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50" style="display: none;" onclick="event.target === this && closeAssignmentModal()">
        <div class="relative mx-auto border shadow-lg rounded-md bg-white" style="margin-top: 80px; margin-bottom: 80px; max-height: calc(100vh - 160px); overflow-y: auto; width: 600px; max-width: 90%; padding: 32px;">
            <div>
                <h3 style="font-size: 20px; font-weight: 600; color: #111827; margin-bottom: 16px;">
                    {{ __('events.assign_users_modal_title') }} - <span id="modalTitle"></span>
                </h3>

                <!-- Capacity indicator -->
                <div id="capacityIndicator" style="margin-bottom: 16px; padding: 12px; border-radius: 6px; font-size: 14px;">
                    <!-- Capacity info will be inserted here -->
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">{{ __('events.select_participants_2') }}</label>
                    <div id="participantsList" style="max-height: 240px; overflow-y: auto; padding: 12px; border: 1px solid #D1D5DB; border-radius: 6px;">
                        <!-- User checkboxes will be inserted here -->
                    </div>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">{{ __('events.select_leader_optional') }}</label>
                    <div id="leadersList" style="max-height: 160px; overflow-y: auto; padding: 12px; border: 1px solid #D1D5DB; border-radius: 6px;">
                        <!-- Leader radio buttons will be inserted here -->
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; width: 100%;">
                    <button type="button" onclick="closeAssignmentModal()" style="display: inline-block; padding: 8px 16px; background-color: #E5E7EB; color: #374151; border-radius: 6px; border: 1px solid #D1D5DB; cursor: pointer; font-size: 14px; font-weight: 500; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#D1D5DB'" onmouseout="this.style.backgroundColor='#E5E7EB'">
                        {{ __('events.cancel') }}
                    </button>
                    <button type="button" onclick="saveAssignment()" style="display: inline-block; padding: 8px 16px; background-color: #4F46E5; color: white; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#4338CA'" onmouseout="this.style.backgroundColor='#4F46E5'">
                        {{ __('events.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('admin.events.assignments.traits.assignment-scripts')
</x-app-layout>
