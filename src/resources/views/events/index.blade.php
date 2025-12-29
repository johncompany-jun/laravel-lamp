<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Open Events') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($events->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($events as $event)
                                <div class="border rounded-lg p-6 hover:shadow-lg transition">
                                    <h3 class="font-semibold text-lg text-gray-900">{{ $event->title }}</h3>
                                    <div class="mt-3 space-y-2">
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Date:</span> {{ $event->event_date->format('M d, Y (D)') }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Time:</span> {{ date('H:i', strtotime($event->start_time)) }} - {{ date('H:i', strtotime($event->end_time)) }}
                                        </p>
                                        @if($event->location)
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">Location:</span> {{ $event->location }}
                                            </p>
                                        @endif
                                        @if($event->notes)
                                            <p class="text-sm text-gray-600 mt-2">
                                                {{ Str::limit($event->notes, 100) }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="mt-4">
                                        <a href="{{ route('events.show', $event) }}"
                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                            View Details & Apply
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $events->links() }}
                        </div>
                    @else
                        <p class="text-gray-500">No open events at the moment.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
