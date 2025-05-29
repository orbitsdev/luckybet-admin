<div>
    <x-admin>
        <div class="flex justify-between items-center mb-4">
            <span class="text-lg font-medium text-gray-700">Low Win Numbers: 
                <span class="ml-2 inline-block bg-primary-100 text-primary-800 text-lg font-semibold px-2.5 py-0.5 rounded">
                    {{ \Carbon\Carbon::parse($this->filterDate)->format('F j, Y') }}
                </span>
            </span>
            <div>
                <x-input.date wire:model.live="filterDate" />
            </div>
        </div>

        <!-- Simple Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Total Low Win Numbers -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-blue-50 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Low Win Numbers</div>
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($lowWinStats['total_low_win_numbers'] ?? 0) }}</div>
                </div>
            </div>
            
            <!-- Total Amount -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-green-50 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Amount</div>
                    <div class="text-2xl font-bold text-green-600">₱{{ number_format($lowWinStats['total_amount'] ?? 0, 2) }}</div>
                </div>
            </div>

            <!-- Average Amount -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-indigo-50 rounded-full p-3">
                        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Average Amount</div>
                    <div class="text-2xl font-bold text-indigo-600">
                        @if(($lowWinStats['total_low_win_numbers'] ?? 0) > 0)
                            ₱{{ number_format(($lowWinStats['total_amount'] ?? 0) / ($lowWinStats['total_low_win_numbers'] ?? 1), 2) }}
                        @else
                            ₱0.00
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Most Common Game Type -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-amber-50 rounded-full p-3">
                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Most Common Game</div>
                    <div class="text-2xl font-bold text-amber-600">
                        @php
                            $mostCommonGame = '';
                            $maxCount = 0;
                            foreach(($lowWinStats['by_game_type'] ?? []) as $game => $count) {
                                if ($count > $maxCount) {
                                    $maxCount = $count;
                                    $mostCommonGame = $game;
                                }
                            }
                        @endphp
                        {{ $mostCommonGame ?: 'None' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div class="lg:col-span-4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="w-full">
                        {{ $this->table }}
                    </div>
                </div>
            </div>
        </div>
    </x-admin>
</div>
