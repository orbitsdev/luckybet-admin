<div>
    <x-admin>
        <div class="flex justify-between items-center mb-4">
            <span class="text-lg font-medium text-gray-700">Bet Ratios:
                <span class="ml-2 inline-block bg-primary-100 text-primary-800 text-lg font-semibold px-2.5 py-0.5 rounded">
                    {{ \Carbon\Carbon::parse($this->filterDate)->format('F j, Y') }}
                </span>
            </span>
        </div>

        <!-- Simple Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Total Bet Ratios -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-blue-50 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Bet Ratios</div>
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($ratioStats['total_ratios'] ?? 0) }}</div>
                </div>
            </div>

            <!-- Total Max Amount -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-green-50 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Max Amount</div>
                    <div class="text-2xl font-bold text-green-600">₱{{ number_format($ratioStats['total_max_amount'] ?? 0, 2) }}</div>
                </div>
            </div>

            <!-- Average Max Amount -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-indigo-50 rounded-full p-3">
                        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Average Max Amount</div>
                    <div class="text-2xl font-bold text-indigo-600">
                        ₱{{ number_format($ratioStats['avg_max_amount'] ?? 0, 2) }}
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
                            foreach(($ratioStats['game_type_counts'] ?? []) as $game) {
                                if ($game['count'] > $maxCount) {
                                    $maxCount = $game['count'];
                                    $mostCommonGame = $game['name'];
                                }
                            }
                        @endphp
                        {{ $mostCommonGame ?: 'None' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- <!-- Main content section with table and detailed stats side by side -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Left column: Detailed Statistics -->
            <div class="lg:col-span-1">
                <!-- Game Type Statistics -->
                @if(count($ratioStats['game_type_counts'] ?? []) > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-medium text-gray-700">Game Type Distribution</h3>
                    </div>
                    <div class="p-4">
                        <ul class="space-y-3">
                            @foreach($ratioStats['game_type_counts'] as $gameType => $data)
                            <li class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color: {{ '#' . substr(md5($gameType), 0, 6) }}"></span>
                                    <span class="text-sm text-gray-600">{{ $gameType }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium bg-blue-50 text-blue-700 px-2 py-0.5 rounded">{{ $data['count'] }}</span>
                                    <span class="text-xs text-gray-500">
                                        @if(($ratioStats['total_ratios'] ?? 0) > 0)
                                            {{ round(($data['count'] / ($ratioStats['total_ratios'] ?? 1)) * 100) }}%
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
                @if(count($ratioStats['location_counts'] ?? []) > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-medium text-gray-700">Location Distribution</h3>
                    </div>
                    <div class="p-4">
                        <ul class="space-y-3">
                            @foreach($ratioStats['location_counts'] as $location => $data)
                            <li class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color: {{ '#' . substr(md5($location), 0, 6) }}"></span>
                                    <span class="text-sm text-gray-600">{{ $location }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium bg-green-50 text-green-700 px-2 py-0.5 rounded">{{ $data['count'] }}</span>
                                    <span class="text-xs text-gray-500">
                                        @if(($ratioStats['total_ratios'] ?? 0) > 0)
                                            {{ round(($data['count'] / ($ratioStats['total_ratios'] ?? 1)) * 100) }}%
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

                <!-- Recent Audit History -->
                @if(count($ratioStats['recent_audits'] ?? []) > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-medium text-gray-700">Recent Changes</h3>
                    </div>
                    <div class="p-4">
                        <ul class="space-y-4">
                            @foreach($ratioStats['recent_audits'] as $audit)
                            <li class="border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="text-xs font-medium text-gray-500">{{ $audit->created_at->format('M j, Y g:i A') }}</span>
                                        <div class="mt-1">
                                            <span class="text-sm font-medium text-gray-700">{{ $audit->betRatio->gameType->name ?? 'Unknown' }} - {{ $audit->betRatio->bet_number ?? 'Unknown' }}</span>
                                        </div>
                                        <div class="mt-1 flex items-center">
                                            <span class="text-xs text-red-500 line-through mr-2">₱{{ number_format($audit->old_max_amount, 2) }}</span>
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            <span class="text-xs text-green-500 ml-2">₱{{ number_format($audit->new_max_amount, 2) }}</span>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $audit->action === 'update' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ ucfirst($audit->action) }}
                                    </span>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div> --}}

            <!-- Right column: Bet Ratios table -->
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
