<div>
    <x-admin>
        <div class="flex justify-between items-center mb-3">
            <span class="text-lg font-medium text-gray-700">Bet Date: 
                <span class="ml-2 inline-block bg-primary-100 text-primary-800 text-lg font-semibold px-2.5 py-0.5 rounded">
                    {{ \Carbon\Carbon::parse($this->filterDate)->format('F j, Y') }}
                </span>
            </span>
        </div>

        <!-- Main content in 3-column grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Left column: Statistics -->
            <div class="col-span-1 mb-4 bg-gray-50 rounded-xl shadow-sm ">
                <!-- Summary Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                    <!-- Total Bets -->
                    <div class="rounded-xl shadow p-3 text-center bg-white text-gray-800">
                        <div class="flex justify-center mb-2">
                            <div class="bg-gray-100 rounded-full p-2">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                        </div>
                        <div class="text-xs font-semibold text-gray-500">Total Bets</div>
                        <div class="text-xl font-bold">{{ number_format($betStats['total_bets'] ?? 0) }}</div>
                    </div>
                    <!-- Total Amount -->
                    <div class="rounded-xl shadow p-3 text-center bg-white text-gray-800">
                        <div class="flex justify-center mb-2">
                            <div class="bg-gray-100 rounded-full p-2">
                                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                        <div class="text-xs font-semibold text-gray-500">Total Amount</div>
                        <div class="text-xl font-bold">₱{{ number_format($betStats['total_amount'] ?? 0, 2) }}</div>
                    </div>
                    <!-- Total Winning Amount -->
                    <div class="rounded-xl shadow p-3 text-center bg-white text-gray-800">
                        <div class="flex justify-center mb-2">
                            <div class="bg-gray-100 rounded-full p-2">
                                <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                            </div>
                        </div>
                        <div class="text-xs font-semibold text-gray-500">Total Winnings</div>
                        <div class="text-xl font-bold">₱{{ number_format($betStats['total_winning_amount'] ?? 0, 2) }}</div>
                    </div>
                </div>

                <!-- Game Type Statistics -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-4">
                    <div class="bg-gray-50 px-3 py-2 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-800">Game Type Statistics</h3>
                    </div>
                    <div class="p-3">
                        @if(!empty($betStats['game_type_counts']))
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Game Type</th>
                                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Count</th>
                                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($betStats['game_type_counts'] as $gameType)
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-900">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        {{ $gameType['code'] === 'S2' ? 'bg-green-100 text-green-800' : 
                                                           ($gameType['code'] === 'S3' ? 'bg-yellow-100 text-yellow-800' : 
                                                           ($gameType['code'] === 'D4' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}"
                                                    >
                                                        {{ $gameType['name'] }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-right text-gray-700">{{ number_format($gameType['count']) }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-right text-gray-700">₱{{ number_format($gameType['total_amount'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 text-gray-500">No game type data available for the selected date.</div>
                        @endif
                    </div>
                </div>

                <!-- Location Statistics -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-4">
                    <div class="bg-gray-50 px-3 py-2 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-800">Location Statistics</h3>
                    </div>
                    <div class="p-3">
                        @if(!empty($betStats['location_counts']))
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Location</th>
                                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Count</th>
                                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($betStats['location_counts'] as $location)
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-900">{{ $location['name'] }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-right text-gray-700">{{ number_format($location['count']) }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-right text-gray-700">₱{{ number_format($location['total_amount'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 text-gray-500">No location data available for the selected date.</div>
                        @endif
                    </div>
                </div>

                <!-- Teller Statistics -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-3 py-2 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-800">Teller Statistics</h3>
                    </div>
                    <div class="p-3">
                        @if(!empty($betStats['teller_counts']))
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Teller</th>
                                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Count</th>
                                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($betStats['teller_counts'] as $teller)
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-900">{{ $teller['name'] }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-right text-gray-700">{{ number_format($teller['count']) }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-right text-gray-700">₱{{ number_format($teller['total_amount'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 text-gray-500">No teller data available for the selected date.</div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Right column: Bets table -->
            <div class="mb-4 bg-white rounded-xl overflow-hidden col-span-1 md:col-span-2 shadow">
                {{ $this->table }}
            </div>
        </div>
    </x-admin>
</div>
