<div>
    <x-admin>
        <div class="mb-4">
            <!-- Teller Statistics -->
            <div class="grid grid-cols-3 gap-4">
                <!-- Total Tellers -->
                <div class="rounded-xl shadow p-4 text-center bg-white text-gray-800">
                    <div class="flex justify-center mb-2">
                        <div class="bg-gray-100 rounded-full p-2">
                            <!-- Users icon -->
                            <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-6a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 10-8 0 4 4 0 008 0z"/></svg>
                        </div>
                    </div>
                    <div class="text-xs font-semibold text-gray-500">Total Tellers</div>
                    <div class="text-2xl font-bold">{{ $tellerStats['total'] ?? '-' }}</div>
                </div>
                <!-- Active Tellers -->
                <div class="rounded-xl shadow p-4 text-center bg-white text-gray-800">
                    <div class="flex justify-center mb-2">
                        <div class="bg-gray-100 rounded-full p-2">
                            <!-- Check icon -->
                            <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                    </div>
                    <div class="text-xs font-semibold text-gray-500">Active Tellers</div>
                    <div class="text-2xl font-bold">{{ $tellerStats['active'] ?? '-' }}</div>
                </div>
                <!-- Inactive Tellers -->
                <div class="rounded-xl shadow p-4 text-center bg-white text-gray-800">
                    <div class="flex justify-center mb-2">
                        <div class="bg-gray-100 rounded-full p-2">
                            <!-- X icon -->
                            <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                    </div>
                    <div class="text-xs font-semibold text-gray-500">Inactive Tellers</div>
                    <div class="text-2xl font-bold">{{ $tellerStats['inactive'] ?? '-' }}</div>
                </div>
            </div>
        </div>
        
        <!-- Filament Table -->
        {{ $this->table }}
    </x-admin>
</div>
