<div>
    <x-admin>
        <div class="flex justify-between items-center mb-4">
            <span class="text-lg font-medium text-gray-700">Sold Out Numbers:
                <span class="ml-2 inline-block bg-primary-100 text-primary-800 text-lg font-semibold px-2.5 py-0.5 rounded">
                    {{ \Carbon\Carbon::parse($filterDate)->format('F j, Y') }}
                </span>
            </span>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Total Sold Out Numbers -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-red-50 rounded-full p-3">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Sold Out Numbers</div>
                    <div class="text-2xl font-bold text-red-600">{{ $soldOutStats['total_sold_out'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Game Type Distribution -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-blue-50 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">By Bet Type</div>
                    <div class="mt-2">
                        @php
                            $gameTypeCounts = $soldOutStats['game_type_counts'] ?? [];
                            $topGameTypes = array_slice($gameTypeCounts, 0, 3, true);
                        @endphp
                        @forelse($topGameTypes as $gameType => $data)
                            <div class="flex justify-between items-center text-sm">
                                <span class="font-medium text-gray-600">{{ $gameType }}</span>
                                <span class="font-bold text-blue-600">{{ $data['count'] ?? 0 }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No data available</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Location Distribution -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-green-50 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">By Location</div>
                    <div class="mt-2">
                        @php
                            $locationCounts = $soldOutStats['location_counts'] ?? [];
                            $topLocations = array_slice($locationCounts, 0, 3, true);
                        @endphp
                        @forelse($topLocations as $location => $data)
                            <div class="flex justify-between items-center text-sm">
                                <span class="font-medium text-gray-600">{{ $location }}</span>
                                <span class="font-bold text-green-600">{{ $data['count'] ?? 0 }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No data available</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Sold Out Numbers table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3">
                <h3 class="text-sm font-medium text-gray-700">Sold Out Numbers</h3>
                <p class="text-xs text-gray-500 mt-1">Numbers that have reached their maximum bet amount</p>
            </div>
            <div class="w-full">
                {{ $this->table }}
            </div>
        </div>

        <x-filament-actions::modals />
    </x-admin>
</div>
