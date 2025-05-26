<div>
    <x-admin>
        <div class="mb-4">
    <!-- Gradient divider line -->
    <!-- <div class="h-1 w-full rounded mb-6" style="background: linear-gradient(90deg, #f43f5e 0%, #22c55e 100%);"></div> -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- Total Users -->
        <div class="rounded-xl shadow p-4 text-center bg-white text-gray-800 ">
            <div class="flex justify-center mb-2">
                <div class="bg-gray-100 rounded-full p-2">
                    <!-- Users icon -->
                    <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-6a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 10-8 0 4 4 0 008 0z"/></svg>
                </div>
            </div>
            <div class="text-xs font-semibold text-gray-500">Total Users</div>
            <div class="text-2xl font-bold">{{ $userStats['total'] ?? '-' }}</div>
        </div>
        <!-- Active Users -->
        <div class="rounded-xl shadow p-4 text-center bg-white text-gray-800 ">
            <div class="flex justify-center mb-2">
                <div class="bg-gray-100 rounded-full p-2">
                    <!-- Check icon -->
                    <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
            <div class="text-xs font-semibold text-gray-500">Active Users</div>
            <div class="text-2xl font-bold">{{ $userStats['active'] ?? '-' }}</div>
        </div>
        <!-- Inactive Users -->
        <div class="rounded-xl shadow p-4 text-center bg-white text-gray-800 ">
            <div class="flex justify-center mb-2">
                <div class="bg-gray-100 rounded-full p-2">
                    <!-- X icon -->
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
            </div>
            <div class="text-xs font-semibold text-gray-500">Inactive Users</div>
            <div class="text-2xl font-bold">{{ $userStats['inactive'] ?? '-' }}</div>
        </div>
        <!-- Coordinators -->
        <div class="rounded-xl shadow p-4 text-center bg-white text-gray-800 ">
            <div class="flex justify-center mb-2">
                <div class="bg-gray-100 rounded-full p-2">
                    <!-- User Group icon -->
                    <svg class="w-7 h-7 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-6a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 10-8 0 4 4 0 008 0z"/></svg>
                </div>
            </div>
            <div class="text-xs font-semibold text-gray-500">Coordinators</div>
            <div class="text-xl font-bold">{{ $userStats['coordinators'] ?? '-' }}</div>
        </div>
        <!-- Tellers -->
        <div class="rounded-xl shadow p-4 text-center bg-white text-gray-800 ">
            <div class="flex justify-center mb-2">
                <div class="bg-gray-100 rounded-full p-2">
                    <!-- Cash icon -->
                    <svg class="w-7 h-7 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a5 5 0 00-10 0v2a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2z"/></svg>
                </div>
            </div>
            <div class="text-xs font-semibold text-gray-500">Tellers</div>
            <div class="text-xl font-bold">{{ $userStats['tellers'] ?? '-' }}</div>
        </div>
    </div>
</div>
        {{ $this->table }}
    </x-admin>
</div>
