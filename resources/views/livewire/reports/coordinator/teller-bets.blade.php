<div class="p-4">
    <div class="mb-4">
        <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <h2 class="text-xl font-semibold text-gray-800">{{ $teller_name }}</h2>
                <p class="text-sm text-gray-600">Bets for {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</p>
            </div>
        </div>
    </div>
    
    <div class="mb-4">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if(count($bets) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Draw Time</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game Type</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bet Number</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Winning</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($bets as $bet)
                                <tr class="{{ $bet->winning_amount > 0 ? 'bg-green-50' : '' }}">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $bet->id }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($bet->draw->draw_time)->format('g:i A') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        {{ $bet->gameType->name }}
                                        @if($bet->d4_sub_selection)
                                            <span class="text-xs text-gray-500">({{ $bet->d4_sub_selection }})</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $bet->bet_number }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-blue-600 font-medium">{{ number_format($bet->amount, 2) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm {{ $bet->winning_amount > 0 ? 'text-green-600 font-bold' : 'text-gray-500' }}">
                                        {{ $bet->winning_amount > 0 ? number_format($bet->winning_amount, 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        @if($bet->winning_amount > 0)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Winner
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Placed
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($bet->created_at)->format('g:i A') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Total Bets:</span>
                            <span class="ml-2 text-sm font-bold text-gray-900">{{ count($bets) }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Total Amount:</span>
                            <span class="ml-2 text-sm font-bold text-blue-600">{{ number_format($bets->sum('amount'), 2) }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Total Winnings:</span>
                            <span class="ml-2 text-sm font-bold text-green-600">{{ number_format($bets->sum('winning_amount'), 2) }}</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="px-4 py-6 text-center text-gray-500">
                    <p>No bets found for this teller on the selected date.</p>
                </div>
            @endif
        </div>
    </div>
</div>
