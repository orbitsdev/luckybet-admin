<div>
    <x-admin>
        <div class="p-4 bg-white rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-3">
                    <a href="{{ url()->previous() }}" class="inline-flex items-center px-3 py-1.5  rounded-md font-medium text-sm text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                        <p class="text-sm text-gray-600">Coordinator: <span class="font-medium">{{ $coordinatorName }}</span></p>
                        <p class="text-sm text-gray-600">Bets for {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-medium text-gray-800">Filters</h3>
                    <button wire:click="resetFilters" class="inline-flex items-center px-3 py-1.5 bg-gray-100 border border-gray-300 rounded-md font-medium text-sm text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset Filters
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" wire:model.live="date" id="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label for="filterStatus" class="block text-sm font-medium text-gray-700">Status</label>
                        <select wire:model.live="filterStatus" id="filterStatus" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            <option value="all">All Bets</option>
                            <option value="winners">Winners Only</option>
                            <option value="potential_winners">Potential Winners</option>
                            <option value="non_winners">Non-Winners</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="filterGameType" class="block text-sm font-medium text-gray-700">Game Type</label>
                        <select wire:model.live="filterGameType" id="filterGameType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            <option value="all">All Game Types</option>
                            @foreach($gameTypes as $gameType)
                                <option value="{{ $gameType }}">{{ $gameType }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="filterDrawTime" class="block text-sm font-medium text-gray-700">Draw Time</label>
                        <select wire:model.live="filterDrawTime" id="filterDrawTime" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            <option value="all">All Draw Times</option>
                            @foreach($drawTimes as $drawTime)
                                <option value="{{ $drawTime }}">{{ \Carbon\Carbon::parse($drawTime)->format('g:i A') }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="searchTerm" class="block text-sm font-medium text-gray-700">Search Ticket ID or Bet Number</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="searchTerm" id="searchTerm" class="block w-full pl-10 pr-3 py-2 rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="Enter ticket ID or bet number">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-4 gap-4">
                        <div class="bg-white p-3 rounded-md shadow-sm">
                            <h3 class="text-sm font-medium text-gray-500">Total Sales</h3>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($totalAmount, 2) }}</p>
                        </div>
                        <div class="bg-white p-3 rounded-md shadow-sm">
                            <h3 class="text-sm font-medium text-gray-500">Total Hits</h3>
                            <p class="text-2xl font-bold text-red-600">{{ number_format($totalWinnings, 2) }}</p>
                        </div>
                        <div class="bg-white p-3 rounded-md shadow-sm">
                            <h3 class="text-sm font-medium text-gray-500">Total Gross</h3>
                            <p class="text-2xl font-bold {{ $totalGross >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($totalGross, 2) }}</p>
                        </div>
                        <div class="bg-white p-3 rounded-md shadow-sm">
                            <h3 class="text-sm font-medium text-gray-500">Total Commission</h3>
                            <p class="text-2xl font-bold text-purple-600">{{ number_format($totalCommission, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                @if(isset($groupedBets) && $groupedBets->count() > 0)
                    @foreach($groupedBets as $drawTime => $drawBets)
                        <div class="mb-6">
                            <div class="flex items-center justify-between bg-gray-100 px-4 py-2 rounded-t-lg border border-gray-200">
                                <h3 class="text-lg font-medium text-gray-800">{{ \Carbon\Carbon::parse($drawTime)->format('g:i A') }} Draw</h3>
                                <div class="flex space-x-4">
                                    <div class="text-sm">
                                        <span class="text-gray-500">Sales:</span>
                                        <span class="font-medium text-blue-600">{{ number_format($drawBets->sum('amount'), 2) }}</span>
                                    </div>
                                    <div class="text-sm">
                                        <span class="text-gray-500">Hits:</span>
                                        <span class="font-medium text-red-600">{{ number_format($drawBets->sum('winning_amount'), 2) }}</span>
                                    </div>
                                    @php $drawGross = $drawBets->sum('amount') - $drawBets->sum('winning_amount'); @endphp
                                    <div class="text-sm">
                                        <span class="text-gray-500">Gross:</span>
                                        <span class="font-medium {{ $drawGross >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($drawGross, 2) }}</span>
                                    </div>
                                    <div class="text-sm">
                                        <span class="text-gray-500">Bets:</span>
                                        <span class="font-medium text-gray-800">{{ $drawBets->count() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto bg-white rounded-b-lg border border-gray-200 border-t-0">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket ID</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game Type</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bet Number</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hits</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comm. Rate</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claimed</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claimed At</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($drawBets as $bet)
                                            <tr class="{{ $bet->winning_amount > 0 ? 'bg-green-50' : ($bet->is_rejected ? 'bg-red-50' : '') }}">
                                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                    {{ ($this->viewBetAction)(['bet_id' => $bet->id]) }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $bet->ticket_id }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $bet->gameType->name }}
                                                    @if($bet->d4_sub_selection)
                                                        <span class="text-xs text-gray-500">({{ $bet->d4_sub_selection }})</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $bet->bet_number }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-blue-600 font-medium">{{ number_format($bet->amount, 2) }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm {{ $bet->winning_amount > 0 ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                                    {{ $bet->winning_amount > 0 ? number_format($bet->winning_amount, 2) : '-' }}
                                                </td>
                                                @php $grossAmount = $bet->amount - $bet->winning_amount; @endphp
                                                <td class="px-4 py-3 whitespace-nowrap text-sm {{ $grossAmount >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                                    {{ number_format($grossAmount, 2) }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-purple-600">
                                                    {{ $bet->commission_rate ? number_format($bet->commission_rate * 100, 0) . '%' : '-' }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-purple-600 font-medium">
                                                    {{ $bet->commission_amount ? number_format($bet->commission_amount, 2) : '-' }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                    @if($bet->is_rejected)
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                            Rejected
                                                        </span>
                                                    @elseif($bet->is_actual_winner)
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            Winner
                                                        </span>
                                                    @elseif($bet->winning_amount > 0 && !$bet->draw->result)
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                            Potential Winner
                                                        </span>
                                                    @else
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                            Placed
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                    @if($bet->is_actual_winner)
                                                        @if($bet->is_claimed)
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                                Yes
                                                            </span>
                                                        @else
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                                No
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                    @if($bet->winning_amount > 0 && $bet->is_claimed && $bet->claimed_at)
                                                        {{ \Carbon\Carbon::parse($bet->claimed_at)->format('M j, g:i A') }}
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                    
                    <div class="bg-white px-4 py-3 border border-gray-200 rounded-lg">
                        {{ $bets->links() }}
                    </div>
                @elseif($bets->count() > 0)
                    <!-- Fallback to non-grouped display if grouping fails -->
                    <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket ID</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Draw Time</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game Type</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bet Number</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hits</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comm. Rate</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claimed</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claimed At</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($bets as $bet)
                                    <tr class="{{ $bet->winning_amount > 0 ? 'bg-green-50' : ($bet->is_rejected ? 'bg-red-50' : '') }}">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            {{ ($this->viewBetAction)(['bet_id' => $bet->id]) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $bet->ticket_id }}</td>
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
                                        <td class="px-4 py-3 whitespace-nowrap text-sm {{ $bet->winning_amount > 0 ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                            {{ $bet->winning_amount > 0 ? number_format($bet->winning_amount, 2) : '-' }}
                                        </td>
                                        @php $grossAmount = $bet->amount - $bet->winning_amount; @endphp
                                        <td class="px-4 py-3 whitespace-nowrap text-sm {{ $grossAmount >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                            {{ number_format($grossAmount, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-purple-600">
                                            {{ $bet->commission_rate ? number_format($bet->commission_rate * 100, 0) . '%' : '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-purple-600 font-medium">
                                            {{ $bet->commission_amount ? number_format($bet->commission_amount, 2) : '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            @if($bet->is_rejected)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Rejected
                                                </span>
                                            @elseif($bet->is_actual_winner)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Winner
                                                </span>
                                            @elseif($bet->winning_amount > 0 && !$bet->draw->result)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Potential Winner
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Placed
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            @if($bet->is_actual_winner)
                                                @if($bet->is_claimed)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Yes
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        No
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            @if($bet->is_actual_winner && $bet->is_claimed && $bet->claimed_at)
                                                {{ \Carbon\Carbon::parse($bet->claimed_at)->format('M j, g:i A') }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
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
                    <div class="px-4 py-6 text-center text-gray-500 bg-white rounded-lg border border-gray-200">
                        <p>No bets found matching your criteria.</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Filament Actions Modals -->
        <x-filament-actions::modals />
    </x-admin>
</div>
