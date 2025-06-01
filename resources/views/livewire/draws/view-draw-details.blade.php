<div>
    <x-admin>
        <div class="container mx-auto py-6">
            <!-- Header with back button -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Draw Details</h1>
                <div>
                    <x-filament::button
                        color="gray"
                        icon="heroicon-o-arrow-left"
                        tag="a"
                        href="{{ route('manage.draws') }}"
                    >
                        Back to Draws
                    </x-filament::button>
                    <x-filament::button
                        color="primary"
                        icon="heroicon-o-pencil"
                        tag="a"
                        href="{{ route('manage.draws.edit', ['draw' => $draw->id]) }}"
                    >
                        Edit Draw
                    </x-filament::button>
                </div>
            </div>

            <!-- Draw Information Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <x-heroicon-o-information-circle class="w-5 h-5 mr-2 text-primary-500" />
                    Draw Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Draw Date</h3>
                        <p class="mt-1 text-base font-medium">{{ $draw->draw_date->format('F j, Y') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Draw Time</h3>
                        <p class="mt-1 text-base font-medium">{{ $draw->draw_time }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Status</h3>
                        <p class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $draw->is_open ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $draw->is_open ? 'Open' : 'Closed' }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Active</h3>
                        <p class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $draw->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $draw->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Winning Numbers Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <x-heroicon-o-trophy class="w-5 h-5 mr-2 text-primary-500" />
                    Winning Numbers
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">S2 (2-Digit)</h3>
                        @if($draw->result && $draw->result->s2_winning_number)
                            <p class="text-2xl font-bold text-green-600">{{ $draw->result->s2_winning_number }}</p>
                        @else
                            <p class="text-gray-400 italic">Not set</p>
                        @endif
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">S3 (3-Digit)</h3>
                        @if($draw->result && $draw->result->s3_winning_number)
                            <p class="text-2xl font-bold text-amber-600">{{ $draw->result->s3_winning_number }}</p>
                        @else
                            <p class="text-gray-400 italic">Not set</p>
                        @endif
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">D4 (4-Digit)</h3>
                        @if($draw->result && $draw->result->d4_winning_number)
                            <p class="text-2xl font-bold text-red-600">{{ $draw->result->d4_winning_number }}</p>
                        @else
                            <p class="text-gray-400 italic">Not set</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bet Ratios Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <x-heroicon-o-currency-dollar class="w-5 h-5 mr-2 text-primary-500" />
                    Bet Ratios
                </h2>
                @if($draw->betRatios->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtype</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Amount</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($draw->betRatios as $ratio)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ratio->location->name ?? 'Unknown' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ratio->gameType->name ?? 'Unknown' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ratio->bet_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ratio->sub_selection ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($ratio->max_amount) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($ratio->max_amount == 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Sold Out</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Available</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-gray-500 italic">No bet ratios defined for this draw.</div>
                @endif
            </div>

            <!-- Low Win Numbers Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <x-heroicon-o-arrow-trending-down class="w-5 h-5 mr-2 text-primary-500" />
                    Low Win Numbers
                </h2>
                @if($draw->lowWinNumbers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Winning Amount</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($draw->lowWinNumbers as $lowWin)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $lowWin->location->name ?? 'Unknown' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lowWin->gameType->name ?? 'Unknown' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lowWin->bet_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($lowWin->winning_amount) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lowWin->reason ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-gray-500 italic">No low win numbers defined for this draw.</div>
                @endif
            </div>

            <!-- Bets Summary Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                        <x-heroicon-o-chart-bar class="w-5 h-5 mr-2 text-primary-500" />
                        Bets Summary
                    </h2>
                    <button 
                        wire:click="toggleDetailedStats"
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        {{ $showDetailedStats ? 'Show Less' : 'Show More' }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="ml-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $showDetailedStats ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
                        </svg>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Bets</h3>
                        <p class="text-2xl font-bold">{{ number_format($betStats['total_bets']) }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Amount</h3>
                        <p class="text-2xl font-bold">{{ number_format($betStats['total_amount']) }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Winnings</h3>
                        <p class="text-2xl font-bold text-{{ $betStats['total_winning_amount'] > 0 ? 'red' : 'green' }}-600">{{ number_format($betStats['total_winning_amount']) }}</p>
                    </div>
                </div>
                
                @if($showDetailedStats || count($betStats['game_types']) <= 8)
                    <!-- Bets by Game Type -->
                    <div class="mb-6">
                        <h3 class="text-md font-medium text-gray-700 mb-3">Bets by Game Type</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            @forelse($betStats['game_types'] as $gameType => $stats)
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium">{{ $gameType }}</span>
                                        <span class="text-sm font-bold">{{ number_format($stats->count) }}</span>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Amount: {{ number_format($stats->amount) }}
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-4 text-center py-4 text-gray-500 italic">No bets found for this draw.</div>
                            @endforelse
                        </div>
                    </div>
                @endif
                
                @if($showDetailedStats)
                    <!-- Bets by Location -->
                    <div class="mb-6">
                        <h3 class="text-md font-medium text-gray-700 mb-3">Bets by Location (Top 10)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            @forelse($betStats['locations'] as $location => $stats)
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium">{{ $location }}</span>
                                        <span class="text-sm font-bold">{{ number_format($stats->count) }}</span>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Amount: {{ number_format($stats->amount) }}
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-4 text-center py-4 text-gray-500 italic">No location data available.</div>
                            @endforelse
                        </div>
                        @if($betStats['has_more_locations'])
                            <div class="mt-2 text-xs text-gray-500 text-right">Showing top 10 locations only</div>
                        @endif
                    </div>
                    
                    <!-- Bets by Teller -->
                    <div>
                        <h3 class="text-md font-medium text-gray-700 mb-3">Bets by Teller (Top 10)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            @forelse($betStats['tellers'] as $teller => $stats)
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium">{{ $teller }}</span>
                                        <span class="text-sm font-bold">{{ number_format($stats->count) }}</span>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Amount: {{ number_format($stats->amount) }}
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-4 text-center py-4 text-gray-500 italic">No teller data available.</div>
                            @endforelse
                        </div>
                        @if($betStats['has_more_tellers'])
                            <div class="mt-2 text-xs text-gray-500 text-right">Showing top 10 tellers only</div>
                        @endif
                    </div>
                @else
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Click "Show More" to view detailed statistics by location and teller</p>
                    </div>
                @endif
            </div>
        </div>
    </x-admin>
</div>
