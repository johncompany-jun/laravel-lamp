<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Welcome to Admin Panel</h3>
                    <p>You are logged in as an administrator.</p>

                    <!-- Quick Actions -->
                    <div class="mt-6 mb-6">
                        <h4 class="font-semibold text-gray-700 mb-3">Quick Actions</h4>
                        <div class="flex gap-3">
                            <a href="{{ route('admin.events.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Create New Event
                            </a>
                            <a href="{{ route('admin.events.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                View All Events
                            </a>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-800">Total Events</h4>
                            <p class="text-2xl font-bold text-blue-900">{{ \App\Models\Event::whereNull('parent_event_id')->count() }}</p>
                        </div>

                        <div class="bg-green-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-800">Open Events</h4>
                            <p class="text-2xl font-bold text-green-900">{{ \App\Models\Event::where('status', 'open')->count() }}</p>
                        </div>

                        <div class="bg-yellow-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-yellow-800">Total Users</h4>
                            <p class="text-2xl font-bold text-yellow-900">{{ \App\Models\User::count() }}</p>
                        </div>

                        <div class="bg-purple-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-purple-800">Applications</h4>
                            <p class="text-2xl font-bold text-purple-900">{{ \App\Models\EventApplication::count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
