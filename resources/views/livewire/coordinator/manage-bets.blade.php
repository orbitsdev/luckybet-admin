<div>


</x-admin>
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Manage Bets</h2>
    
    <!-- Filters -->
    <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 mb-6">
        <div class="md:grid md:grid-cols-4 md:gap-6">
            <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Filters</h3>
                <p class="mt-1 text-sm text-gray-500">Filter bets by various criteria.</p>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-3">
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                        <div class="mt-1">
                            <input type="date" wire:model="date" id="date" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div>
                        <label for="teller_id" class="block text-sm font-medium text-gray-700">Teller</label>
                        <div class="mt-1">
                            <select wire:model="teller_id" id="teller_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <option value="">All Tellers</option>
                                @foreach($tellers as $teller)
                                    <option value="{{ $teller->id }}">{{ $teller->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="game_type_id" class="block text-sm font-medium text-gray-700">Game Type</label>
                        <div class="mt-1">
                            <select wire:model="game_type_id" id="game_type_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <option value="">All Game Types</option>
                                @foreach($gameTypes as $gameType)
                                    <option value="{{ $gameType->id }}">{{ $gameType->name }} ({{ $gameType->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="location_id" class="block text-sm font-medium text-gray-700">Location</label>
                        <div class="mt-1">
                            <select wire:model="location_id" id="location_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <option value="">All Locations</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <div class="mt-1">
                            <input type="text" wire:model.debounce.300ms="search" id="search" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Ticket #, Number, Teller...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
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
    
    <!-- Bets Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('ticket_number')">
                            Ticket #
                            @if ($sortField === 'ticket_number')
                                <span class="ml-1">
                                    @if ($sortDirection === 'asc')
                                        &#8593;
                                    @else
                                        &#8595;
                                    @endif
                                </span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Number
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Game Type
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Draw
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Teller
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('amount')">
                            Amount
                            @if ($sortField === 'amount')
                                <span class="ml-1">
                                    @if ($sortDirection === 'asc')
                                        &#8593;
                                    @else
                                        &#8595;
                                    @endif
                                </span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('winning_amount')">
                            Winning
                            @if ($sortField === 'winning_amount')
                                <span class="ml-1">
                                    @if ($sortDirection === 'asc')
                                        &#8593;
                                    @else
                                        &#8595;
                                    @endif
                                </span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('created_at')">
                            Created
                            @if ($sortField === 'created_at')
                                <span class="ml-1">
                                    @if ($sortDirection === 'asc')
                                        &#8593;
                                    @else
                                        &#8595;
                                    @endif
                                </span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($bets as $bet)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $bet->ticket_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $bet->number_combination }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $bet->gameType->name }} ({{ $bet->gameType->code }})
                                @if($bet->gameType->code === 'D4' && $bet->d4_sub_selection)
                                    <span class="text-xs font-medium bg-blue-100 text-blue-800 px-2 py-0.5 rounded">{{ $bet->d4_sub_selection }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $bet->draw->draw_date->format('M d, Y') }} {{ $bet->draw->draw_time }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $bet->teller->name }}
                                <div class="text-xs text-gray-400">{{ $bet->teller->location->name ?? 'No location' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱{{ number_format($bet->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $bet->winning_amount > 0 ? 'text-green-600 font-medium' : 'text-gray-500' }}">
                                @if($bet->winning_amount > 0)
                                    ₱{{ number_format($bet->winning_amount, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $bet->created_at->format('M d, Y g:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="viewBetDetails({{ $bet->id }})" class="text-indigo-600 hover:text-indigo-900">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No bets found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 sm:px-6">
            {{ $bets->links() }}
        </div>
    </div>
    
    <!-- Bet Details Modal -->
    <div class="fixed inset-0 z-50 overflow-y-auto" style="display: {{ $showBetDetailsModal ? 'block' : 'none' }}">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Bet Details
                            </h3>
                            
                            @if($selectedBet)
                                <div class="mt-4 border-t border-gray-200 pt-4">
                                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500">Ticket Number</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedBet->ticket_number }}</dd>
                                        </div>
                                        
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500">Number Combination</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedBet->number_combination }}</dd>
                                        </div>
                                        
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500">Game Type</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                {{ $selectedBet->gameType->name }} ({{ $selectedBet->gameType->code }})
                                                @if($selectedBet->gameType->code === 'D4' && $selectedBet->d4_sub_selection)
                                                    <span class="text-xs font-medium bg-blue-100 text-blue-800 px-2 py-0.5 rounded">{{ $selectedBet->d4_sub_selection }}</span>
                                                @endif
                                            </dd>
                                        </div>
                                        
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500">Draw</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedBet->draw->draw_date->format('M d, Y') }} {{ $selectedBet->draw->draw_time }}</dd>
                                        </div>
                                        
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500">Teller</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedBet->teller->name }}</dd>
                                        </div>
                                        
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500">Location</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedBet->teller->location->name ?? 'Not assigned' }}</dd>
                                        </div>
                                        
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                            <dd class="mt-1 text-sm text-gray-900">₱{{ number_format($selectedBet->amount, 2) }}</dd>
                                        </div>
                                        
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500">Winning Amount</dt>
                                            <dd class="mt-1 text-sm {{ $selectedBet->winning_amount > 0 ? 'text-green-600 font-medium' : 'text-gray-900' }}">
                                                @if($selectedBet->winning_amount > 0)
                                                    ₱{{ number_format($selectedBet->winning_amount, 2) }}
                                                @else
                                                    ₱0.00
                                                @endif
                                            </dd>
                                        </div>
                                        
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedBet->created_at->format('M d, Y g:i A') }}</dd>
                                        </div>
                                        
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500">Receipt Status</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                @if($selectedBet->receipt)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        {{ ucfirst($selectedBet->receipt->status) }}
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Legacy
                                                    </span>
                                                @endif
                                            </dd>
                                        </div>
                                        
                                        @if($selectedBet->draw->result)
                                            <div class="sm:col-span-2">
                                                <dt class="text-sm font-medium text-gray-500">Draw Result</dt>
                                                <dd class="mt-1 text-sm text-gray-900">{{ $selectedBet->draw->result }}</dd>
                                            </div>
                                        @endif
                                    </dl>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="$set('showBetDetailsModal', false)" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    <x-admin>
</div>
