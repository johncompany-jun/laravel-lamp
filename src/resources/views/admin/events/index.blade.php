<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('events.management') }}
            </h2>
            <a href="{{ route('admin.events.create') }}"
               style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background-color: #4F46E5; color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); transition: background-color 0.2s;"
               onmouseover="this.style.backgroundColor='#4338CA'"
               onmouseout="this.style.backgroundColor='#4F46E5'">
                <span class="material-icons" style="font-size: 20px;">add_circle</span>
                {{ __('events.create_new') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons" style="font-size: 20px;">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($events->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div style="display: flex; align-items: center; gap: 4px;">
                                                <span class="material-icons" style="font-size: 16px;">event</span>
                                                {{ __('events.title') }}
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div style="display: flex; align-items: center; gap: 4px;">
                                                <span class="material-icons" style="font-size: 16px;">calendar_today</span>
                                                {{ __('events.date') }}
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div style="display: flex; align-items: center; gap: 4px;">
                                                <span class="material-icons" style="font-size: 16px;">schedule</span>
                                                {{ __('events.time') }}
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div style="display: flex; align-items: center; gap: 4px;">
                                                <span class="material-icons" style="font-size: 16px;">info</span>
                                                {{ __('events.status') }}
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div style="display: flex; align-items: center; gap: 4px;">
                                                <span class="material-icons" style="font-size: 16px;">people</span>
                                                {{ __('events.applications') }}
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('events.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($events as $event)
                                        @php
                                            $presenter = new \App\Presenters\EventPresenter($event);
                                        @endphp
                                        <tr style="transition: background-color 0.15s;" onmouseover="this.style.backgroundColor='#F9FAFB'" onmouseout="this.style.backgroundColor='white'">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $event->title }}</div>
                                                {!! $presenter->badges() !!}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $presenter->formattedDate() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $presenter->timeRange() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {!! $presenter->statusBadge() !!}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <span class="material-icons" style="font-size: 18px; color: #6B7280;">group</span>
                                                    <span class="text-sm font-medium text-gray-900">{{ $presenter->applicationsCount() }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div style="display: inline-flex; gap: 4px;">
                                                    <a href="{{ route('admin.events.show', $event) }}"
                                                       title="{{ __('events.view_details') }}"
                                                       style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: #EEF2FF; color: #4F46E5; border-radius: 6px; text-decoration: none; transition: background-color 0.2s;"
                                                       onmouseover="this.style.backgroundColor='#E0E7FF'"
                                                       onmouseout="this.style.backgroundColor='#EEF2FF'">
                                                        <span class="material-icons" style="font-size: 18px;">visibility</span>
                                                    </a>

                                                    <a href="{{ route('admin.events.edit', $event) }}"
                                                       title="{{ __('events.edit_event_btn') }}"
                                                       style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: #DBEAFE; color: #1E40AF; border-radius: 6px; text-decoration: none; transition: background-color 0.2s;"
                                                       onmouseover="this.style.backgroundColor='#BFDBFE'"
                                                       onmouseout="this.style.backgroundColor='#DBEAFE'">
                                                        <span class="material-icons" style="font-size: 18px;">edit</span>
                                                    </a>

                                                    <a href="{{ route('admin.events.assignments.create', $event) }}"
                                                       title="{{ __('events.assign_users_btn') }}"
                                                       style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: #D1FAE5; color: #065F46; border-radius: 6px; text-decoration: none; transition: background-color 0.2s;"
                                                       onmouseover="this.style.backgroundColor='#A7F3D0'"
                                                       onmouseout="this.style.backgroundColor='#D1FAE5'">
                                                        <span class="material-icons" style="font-size: 18px;">assignment_ind</span>
                                                    </a>

                                                    <form action="{{ route('admin.events.destroy', $event) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                title="{{ __('events.delete_event') }}"
                                                                onclick="return confirm('{{ __('events.delete_confirm') }}')"
                                                                style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background-color: #FEE2E2; color: #991B1B; border-radius: 6px; border: none; cursor: pointer; transition: background-color 0.2s;"
                                                                onmouseover="this.style.backgroundColor='#FECACA'"
                                                                onmouseout="this.style.backgroundColor='#FEE2E2'">
                                                            <span class="material-icons" style="font-size: 18px;">delete</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $events->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div style="display: flex; justify-content: center; margin-bottom: 16px;">
                                <span class="material-icons" style="font-size: 64px; color: #D1D5DB;">event_busy</span>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('events.no_events') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('events.get_started') }}</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.events.create') }}"
                                   style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background-color: #4F46E5; color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); transition: background-color 0.2s;"
                                   onmouseover="this.style.backgroundColor='#4338CA'"
                                   onmouseout="this.style.backgroundColor='#4F46E5'">
                                    <span class="material-icons" style="font-size: 20px;">add_circle</span>
                                    {{ __('events.create_event') }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
