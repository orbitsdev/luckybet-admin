<div>
    <x-admin>
        <div class="p-4 bg-white rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-3">
                    <a href="{{ url()->previous() }}" class="inline-flex items-center px-2 py-1 text-sm text-gray-700 hover:text-indigo-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800">Teller Bets Report</h1>
                </div>
            </div>
            
            <div class="mb-4 bg-indigo-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-semibold text-gray-800">{{ $tellerName }}</h2>
                        <p class="text-sm text-gray-600">Bets for {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" wire:model.live="date" id="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label for="filterStatus" class="block text-sm font-medium text-gray-700">Status</label>
                        <select wire:model.live="filterStatus" id="filterStatus" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="all">All Bets</option>
                            <option value="winners">Winners Only</option>
                            <option value="non_winners">Non-Winners</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="filterGameType" class="block text-sm font-medium text-gray-700">Game Type</label>
                        <select wire:model.live="filterGameType" id="filterGameType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="all">All Game Types</option>
                            @foreach($gameTypes as $gameType)
                                <option value="{{ $gameType }}">{{ $gameType }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="filterDrawTime" class="block text-sm font-medium text-gray-700">Draw Time</label>
                        <select wire:model.live="filterDrawTime" id="filterDrawTime" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="all">All Draw Times</option>
                            @foreach($drawTimes as $drawTime)
                                <option value="{{ $drawTime }}">{{ \Carbon\Carbon::parse($drawTime)->format('g:i A') }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="searchTerm" class="block text-sm font-medium text-gray-700">Search Bet Number</label>
                        <input type="text" wire:model.live.debounce.300ms="searchTerm" id="searchTerm" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Enter bet number">
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white p-3 rounded-md shadow-sm">
                            <h3 class="text-sm font-medium text-gray-500">Total Amount</h3>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($totalAmount, 2) }}</p>
                        </div>
                        <div class="bg-white p-3 rounded-md shadow-sm">
                            <h3 class="text-sm font-medium text-gray-500">Total Winnings</h3>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($totalWinnings, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-4 bg-white rounded-lg shadow overflow-hidden">
                @if($bets->count() > 0)
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
                                    <tr class="{{ $bet->winning_amount > 0 ? 'bg-green-50' : ($bet->is_rejected ? 'bg-red-50' : '') }}">
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
                                            @if($bet->is_rejected)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Rejected
                                                </span>
                                            @elseif($bet->winning_amount > 0)
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
                    
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $bets->links() }}
                    </div>
                @else
                    <div class="px-4 py-6 text-center text-gray-500">
                        <p>No bets found matching your criteria.</p>
                    </div>
                @endif
            </div>
        </div>
    </x-admin>
</div>
