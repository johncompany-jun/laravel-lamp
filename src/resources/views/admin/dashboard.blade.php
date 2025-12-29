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

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-800">Total Users</h4>
                            <p class="text-2xl font-bold text-blue-900">{{ \App\Models\User::count() }}</p>
                        </div>

                        <div class="bg-green-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-800">Admin Users</h4>
                            <p class="text-2xl font-bold text-green-900">{{ \App\Models\User::where('role', 'admin')->count() }}</p>
                        </div>

                        <div class="bg-purple-100 p-4 rounded-lg">
                            <h4 class="font-semibold text-purple-800">Regular Users</h4>
                            <p class="text-2xl font-bold text-purple-900">{{ \App\Models\User::where('role', 'user')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
