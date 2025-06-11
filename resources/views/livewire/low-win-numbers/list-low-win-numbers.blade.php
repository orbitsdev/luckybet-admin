<div>
    <x-admin>
      
        
        <!-- Enhanced Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Active vs Inactive -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-blue-50 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Active / Inactive</div>
                    <div class="text-2xl font-bold text-gray-800">
                        <span class="text-green-600">{{ number_format($lowWinStats['active_count'] ?? 0) }}</span> / 
                        <span class="text-red-600">{{ number_format($lowWinStats['inactive_count'] ?? 0) }}</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Total: {{ number_format($lowWinStats['total_low_win_numbers'] ?? 0) }}</div>
                </div>
            </div>
            
            <!-- Global vs Draw-Specific -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-purple-50 rounded-full p-3">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Global / Draw-Specific</div>
                    <div class="text-2xl font-bold">
                        <span class="text-purple-600">{{ number_format($lowWinStats['global_count'] ?? 0) }}</span> / 
                        <span class="text-yellow-600">{{ number_format($lowWinStats['draw_specific_count'] ?? 0) }}</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">{{ number_format(($lowWinStats['global_count'] ?? 0) / max(1, ($lowWinStats['total_low_win_numbers'] ?? 1)) * 100, 1) }}% Global</div>
                </div>
            </div>

            <!-- Game Type Distribution -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-indigo-50 rounded-full p-3">
                        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Game Type Distribution</div>
                    <div class="flex justify-center gap-2 mt-1">
                        @foreach(($lowWinStats['by_game_type'] ?? []) as $game => $count)
                            <span class="px-2 py-1 text-xs font-medium rounded {{ $game == 'S2' ? 'bg-blue-100 text-blue-800' : ($game == 'S3' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $game }}: {{ $count }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Total Winning Amount -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-green-50 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Winning Amount</div>
                    <div class="text-2xl font-bold text-green-600">₱{{ number_format($lowWinStats['total_amount'] ?? 0, 2) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Avg: ₱{{ number_format(($lowWinStats['total_amount'] ?? 0) / max(1, ($lowWinStats['total_low_win_numbers'] ?? 1)), 2) }}</div>
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
