<div>
    <x-admin>
        <div class="flex justify-between items-center mb-3">
            <span class="text-lg font-medium text-gray-700">Low Win Numbers: 
                <span class="ml-2 inline-block bg-primary-100 text-primary-800 text-lg font-semibold px-2.5 py-0.5 rounded">
                    {{ \Carbon\Carbon::parse($this->filterDate)->format('F j, Y') }}
                </span>
            </span>
            <div>
                <x-input.date wire:model.live="filterDate" />
            </div>
        </div>

        <!-- Main content in 3-column grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Left column: Statistics -->
            <div class="col-span-1 mb-4 bg-gray-50 rounded-xl shadow-sm">
                <!-- Summary Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <!-- Total Low Win Numbers -->
                    <div class="rounded-xl shadow p-3 text-center bg-white text-gray-800">
                        <div class="flex justify-center mb-2">
                            <div class="bg-gray-100 rounded-full p-2">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                        <div class="text-xs font-semibold text-gray-500">Total Low Win Numbers</div>
                        <div class="text-xl font-bold">{{ number_format($lowWinStats['total_low_win_numbers'] ?? 0) }}</div>
                    </div>
                    
                    <!-- Total Amount -->
                    <div class="rounded-xl shadow p-3 text-center bg-white text-gray-800">
                        <div class="flex justify-center mb-2">
                            <div class="bg-gray-100 rounded-full p-2">
                                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                        <div class="text-xs font-semibold text-gray-500">Total Amount</div>
                        <div class="text-xl font-bold text-green-700">₱{{ number_format($lowWinStats['total_amount'] ?? 0, 2) }}</div>
                    </div>
                </div>

                <!-- Average and Patterns -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-4">
                    <div class="bg-gray-50 px-3 py-2 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-800">Analysis</h3>
                    </div>
                    <div class="p-3">
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Average Amount -->
                            <div class="p-2 rounded-lg bg-indigo-50 border border-indigo-100">
                                <div class="text-xs text-indigo-500 mb-1">Average Amount</div>
                                <div class="text-lg font-bold text-indigo-700">
                                    @if(($lowWinStats['total_low_win_numbers'] ?? 0) > 0)
                                        ₱{{ number_format(($lowWinStats['total_amount'] ?? 0) / ($lowWinStats['total_low_win_numbers'] ?? 1), 2) }}
                                    @else
                                        ₱0.00
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Most Common Game Type -->
                            <div class="p-2 rounded-lg bg-amber-50 border border-amber-100">
                                <div class="text-xs text-amber-500 mb-1">Most Common Game</div>
                                <div class="text-lg font-bold text-amber-700">
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
                </div>

                <!-- Game Type Statistics -->
                @if(count($lowWinStats['by_game_type'] ?? []) > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-4">
                    <div class="bg-gray-50 px-3 py-2 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-800">Game Type Distribution</h3>
                    </div>
                    <div class="p-3">
                        <ul class="divide-y divide-gray-100">
                            @foreach($lowWinStats['by_game_type'] as $gameType => $count)
                            <li class="py-2 flex justify-between items-center">
                                <div class="flex items-center">
                                    <span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color: {{ '#' . substr(md5($gameType), 0, 6) }}"></span>
                                    <span class="text-sm text-gray-600">{{ $gameType }}</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm font-medium bg-blue-50 text-blue-700 px-2 py-1 rounded-lg mr-2">{{ $count }}</span>
                                    <span class="text-xs text-gray-500">
                                        @if(($lowWinStats['total_low_win_numbers'] ?? 0) > 0)
                                            {{ round(($count / ($lowWinStats['total_low_win_numbers'] ?? 1)) * 100) }}%
                                        @else
                                            0%
                                        @endif
                                    </span>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <!-- Location Statistics -->
                @if(count($lowWinStats['by_location'] ?? []) > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-4">
                    <div class="bg-gray-50 px-3 py-2 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-800">Location Distribution</h3>
                    </div>
                    <div class="p-3">
                        <ul class="divide-y divide-gray-100">
                            @foreach($lowWinStats['by_location'] as $location => $count)
                            <li class="py-2 flex justify-between items-center">
                                <div class="flex items-center">
                                    <span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color: {{ '#' . substr(md5($location), 0, 6) }}"></span>
                                    <span class="text-sm text-gray-600">{{ $location }}</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm font-medium bg-green-50 text-green-700 px-2 py-1 rounded-lg mr-2">{{ $count }}</span>
                                    <span class="text-xs text-gray-500">
                                        @if(($lowWinStats['total_low_win_numbers'] ?? 0) > 0)
                                            {{ round(($count / ($lowWinStats['total_low_win_numbers'] ?? 1)) * 100) }}%
                                        @else
                                            0%
                                        @endif
                                    </span>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Right column: Low Win Numbers table -->
            <div class="mb-4  rounded-xl overflow-hidden col-span-1 md:col-span-2 ">
                {{ $this->table }}
            </div>
        </div>
    </x-admin>
</div>
