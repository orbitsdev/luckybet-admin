<div>
    <x-admin>
        <!-- Dashboard Header -->
        <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Admin Dashboard</h1>
                <p class="text-gray-600">Welcome to the LuckyBet Admin Dashboard</p>
            </div>

            <!-- Date Selection UI -->
            <div class=" md:mt-0 flex items-center ">
                <!-- Date Display -->


                <!-- Date Picker -->
                <div>
                    <div class="relative">
                        <input
                            id="date-picker"
                            type="date"
                            wire:model.live="selectedDate"
                            wire:change="loadStats"
                            class="pl-3 pr-10 py-2 border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-gray-400 focus:ring-0"
                        >
                        <button
                            type="button"
                            onclick="document.getElementById('date-picker').showPicker()"
                            class="absolute right-1 top-1/2 -translate-y-1/2 bg-primary-600 text-white p-1 rounded-md hover:bg-primary-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Bets Card -->
            <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">{{ \Carbon\Carbon::parse($selectedDate)->format('M d') }} Bets</p>
                        <p class="text-2xl font-bold">{{ number_format($todayStats['totalBets']) }}</p>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-sm text-gray-600">Total Amount: ₱{{ number_format($todayStats['totalAmount']) }}</p>
                </div>
            </div>

            <!-- Draws Card -->
            <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">{{ \Carbon\Carbon::parse($selectedDate)->format('M d') }} Draws</p>
                        <p class="text-2xl font-bold">{{ $todayStats['upcomingDraws'] + $todayStats['completedDraws'] }}</p>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-sm text-gray-600">
                        <span class="text-green-500">{{ $todayStats['upcomingDraws'] }} Upcoming</span> |
                        <span class="text-blue-500">{{ $todayStats['completedDraws'] }} Completed</span>
                    </p>
                </div>
            </div>

            <!-- Sold Out Numbers Card -->
            <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Sold Out Numbers</p>
                        <p class="text-2xl font-bold">{{ number_format($todayStats['soldOutNumbers']) }}</p>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-sm text-gray-600">
                        <a href="{{ route('manage.sold-out-numbers') }}" class="text-red-500 hover:underline">View Details →</a>
                    </p>
                </div>
            </div>

            <!-- Low Win Numbers Card -->
            <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Low Win Numbers</p>
                        <p class="text-2xl font-bold">{{ number_format($todayStats['lowWinNumbers']) }}</p>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-sm text-gray-600">
                        <a href="{{ route('manage.low-win-numbers') }}" class="text-yellow-500 hover:underline">View Details →</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Draws and Performance Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Today's Draws -->
            <div class="bg-white rounded-lg shadow-md p-4 lg:col-span-2">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }} Draws</h2>
                    <a href="{{ route('manage.draws') }}" class="text-sm text-blue-500 hover:underline">View All</a>
                </div>

                @if(count($drawStats['todayDraws']) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sold Out</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($drawStats['todayDraws'] as $draw)
                                    <tr>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($draw->draw_time)->format('h:i A') }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            @if($draw->is_open)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Open
                                                </span>
                                            @elseif($draw->is_completed)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Completed
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Closed
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                            {{ count($draw->betRatios) }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm">
                                            <a href="{{ route('manage.draws.view', $draw) }}" class="text-blue-500 hover:underline mr-2">View</a>
                                            <a href="{{ route('manage.draws.edit', $draw) }}" class="text-green-500 hover:underline">Edit</a>
                                        </td>
                                    </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-gray-500">
                        No draws scheduled for today
                    </div>
                @endif

                <!-- Tomorrow's Draws Preview -->
                @if(count($drawStats['tomorrowDraws']) > 0)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <h3 class="text-sm font-medium text-gray-600 mb-2">Tomorrow's Draws</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($drawStats['tomorrowDraws'] as $draw)
                                <span class="px-2 py-1 bg-gray-100 rounded text-xs text-gray-700">
                                    {{ \Carbon\Carbon::parse($draw->draw_time)->format('h:i A') }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Top Locations -->
            <div class="bg-white rounded-lg shadow-md p-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Top Locations - {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</h2>

                @if(count($locationStats['topLocations']) > 0)
                    <div class="space-y-4">
                        @foreach($locationStats['topLocations'] as $location)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-700">{{ $location->name }}</p>
                                    <p class="text-sm text-gray-500">{{ number_format($location->bet_count) }} bets</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-800">₱{{ number_format($location->total_amount) }}</p>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <hr class="border-gray-200">
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-gray-500">
                        No location data available
                    </div>
                @endif
            </div>
        </div>

        <!-- Top Tellers, Recent Winners and Game Type Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Top Tellers -->
            <div class="bg-white rounded-lg shadow-md p-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Top Tellers - {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</h2>

                @if(count($userStats['topTellers']) > 0)
                    <div class="space-y-4">
                        @foreach($userStats['topTellers'] as $teller)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-700">{{ $teller->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $teller->location_name ?? 'No location' }}</p>
                                    <p class="text-sm text-gray-500">{{ number_format($teller->bet_count) }} bets</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-800">₱{{ number_format($teller->total_amount) }}</p>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <hr class="border-gray-200">
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-gray-500">
                        No teller data available
                    </div>
                @endif
            </div>

            <!-- Recent Winners -->
            <div class="bg-white rounded-lg shadow-md p-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Winners - {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</h2>

                @if(count($userStats['recentWinners']) > 0)
                    <div class="space-y-4">
                        @foreach($userStats['recentWinners'] as $winner)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-700">{{ $winner->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $winner->location_name ?? 'No location' }}</p>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded-full">{{ $winner->game_type }}{{ $winner->d4_sub_selection ? '-'.$winner->d4_sub_selection : '' }}</span>
                                        <span class="text-sm text-gray-500">{{ $winner->bet_number }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-green-600">₱{{ number_format($winner->winning_amount) }}</p>
                                    <p class="text-xs {{ $winner->is_claimed ? 'text-green-500' : 'text-yellow-500' }}">
                                        {{ $winner->is_claimed ? 'Claimed' : 'Unclaimed' }}
                                    </p>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <hr class="border-gray-200">
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-gray-500">
                        No winners data available
                    </div>
                @endif
            </div>

            <!-- Game Type Distribution -->
            <div class="bg-white rounded-lg shadow-md p-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Bet Type Distribution - {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</h2>

                @if(count($drawStats['gameTypeDistribution']) > 0)
                    <div class="space-y-4">
                        @php
                            $colors = [
                                'S2' => 'bg-green-500',
                                'S3' => 'bg-yellow-500',
                                'D4' => 'bg-blue-500',
                                'D4-S2' => 'bg-purple-500',
                                'D4-S3' => 'bg-indigo-500',
                            ];
                            $total = array_sum(array_column($drawStats['gameTypeDistribution'], 'count'));
                        @endphp

                        @foreach($drawStats['gameTypeDistribution'] as $name => $data)
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $name }}</span>
                                    <span class="text-sm text-gray-500">{{ number_format($data['count']) }} ({{ round(($data['count'] / $total) * 100) }}%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="{{ $colors[$name] ?? 'bg-gray-500' }} h-2.5 rounded-full" style="width: {{ ($data['count'] / $total) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-gray-500">
                        No game type data available
                    </div>
                @endif

                <!-- Quick Links -->
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-600 mb-3">Quick Links</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('manage.draws') }}" class="text-sm text-blue-500 hover:underline flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Manage Draws
                        </a>
                        <a href="{{ route('manage.bets') }}" class="text-sm text-blue-500 hover:underline flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            View Bets
                        </a>
                        <a href="{{ route('manage.users') }}" class="text-sm text-blue-500 hover:underline flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Manage Users
                        </a>
                        <a href="{{ route('manage.locations') }}" class="text-sm text-blue-500 hover:underline flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Locations
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-admin>
</div>
