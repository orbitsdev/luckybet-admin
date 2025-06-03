<div>
    <x-admin>


    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Coordinator Dashboard</h2>
    
    <!-- Date Filter -->
    <div class="mb-6">
        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
        <input type="date" wire:model="date" id="date" class="mt-1 block w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Bets</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($totalBets) }}</dd>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Amount</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">₱{{ number_format($totalAmount, 2) }}</dd>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Winning Amount</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">₱{{ number_format($totalWinningAmount, 2) }}</dd>
            </div>
        </div>
    </div>
    
    <!-- Teller Performance -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Teller Performance</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Performance metrics for tellers under your coordination.</p>
        </div>
        <div class="border-t border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teller</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bets</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Winning Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profit/Loss</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tellerStats as $teller)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $teller['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($teller['bet_count']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($teller['total_amount'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($teller['total_winning_amount'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $teller['total_amount'] - $teller['total_winning_amount'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ₱{{ number_format($teller['total_amount'] - $teller['total_winning_amount'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Game Type Distribution -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Game Type Distribution</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Breakdown of bets by game type.</p>
        </div>
        <div class="border-t border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bets</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($gameTypeStats as $gameType)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $gameType['name'] }} ({{ $gameType['code'] }})</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($gameType['bet_count']) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($gameType['total_amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Weekly Performance Chart -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Weekly Performance</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Bet activity over the last 7 days.</p>
        </div>
        <div class="border-t border-gray-200 p-6">
            <div class="h-64">
                <!-- This is a placeholder for a chart - in a real implementation, you would use a JS charting library -->
                <div class="flex h-full">
                    @php
                        $startDate = \Carbon\Carbon::today()->subDays(6);
                        $endDate = \Carbon\Carbon::today();
                        $dateRange = [];
                        
                        for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
                            $dateRange[] = $date->format('Y-m-d');
                        }
                        
                        $maxAmount = 0;
                        foreach ($dateRange as $date) {
                            $amount = $dateStats[$date]['total_amount'] ?? 0;
                            $maxAmount = max($maxAmount, $amount);
                        }
                    @endphp
                    
                    @foreach($dateRange as $date)
                        @php
                            $formattedDate = \Carbon\Carbon::parse($date)->format('M d');
                            $count = $dateStats[$date]['bet_count'] ?? 0;
                            $amount = $dateStats[$date]['total_amount'] ?? 0;
                            $height = $maxAmount > 0 ? ($amount / $maxAmount * 100) : 0;
                        @endphp
                        <div class="flex-1 flex flex-col items-center justify-end">
                            <div class="text-xs text-gray-500 mb-1">₱{{ number_format($amount) }}</div>
                            <div class="w-full bg-indigo-600 rounded-t" style="height: {{ $height }}%"></div>
                            <div class="text-xs text-gray-500 mt-2">{{ $formattedDate }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-admin>
</div>
