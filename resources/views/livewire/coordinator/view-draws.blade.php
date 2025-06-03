<div>
    <x-admin>
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">View Draws</h2>
    
    <!-- Filters -->
    <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 mb-6">
        <div class="md:grid md:grid-cols-4 md:gap-6">
            <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Filters</h3>
                <p class="mt-1 text-sm text-gray-500">Filter draws by date and time.</p>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-3">
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                        <div class="mt-1">
                            <input type="date" wire:model="date" id="date" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div>
                        <label for="draw_time" class="block text-sm font-medium text-gray-700">Draw Time</label>
                        <div class="mt-1">
                            <select wire:model="draw_time" id="draw_time" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <option value="">All Times</option>
                                @foreach($drawTimes as $time)
                                    <option value="{{ $time }}">{{ $time }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Draws Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('draw_date')">
                            Date
                            @if ($sortField === 'draw_date')
                                <span class="ml-1">
                                    @if ($sortDirection === 'asc')
                                        &#8593;
                                    @else
                                        &#8595;
                                    @endif
                                </span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('draw_time')">
                            Time
                            @if ($sortField === 'draw_time')
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
                            Result
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Bets
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Amount
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Winning Amount
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Profit/Loss
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($draws as $draw)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $draw->draw_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $draw->draw_time }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($draw->result)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ $draw->result }}
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($draw->bets_count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱{{ number_format($draw->bets_sum_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱{{ number_format($draw->bets_sum_winning_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ ($draw->bets_sum_amount - $draw->bets_sum_winning_amount) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ₱{{ number_format($draw->bets_sum_amount - $draw->bets_sum_winning_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="viewDrawDetails({{ $draw->id }})" class="text-indigo-600 hover:text-indigo-900">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No draws found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 sm:px-6">
            {{ $draws->links() }}
        </div>
    </div>
    
    <!-- Draw Details Modal -->
    <div class="fixed inset-0 z-50 overflow-y-auto" style="display: {{ $showDrawDetailsModal ? 'block' : 'none' }}">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Draw Details
                            </h3>
                            
                            @if($selectedDraw)
                                <div class="mt-4 border-t border-gray-200 pt-4">
                                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500">Date</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedDraw->draw_date->format('M d, Y') }}</dd>
                                        </div>
                                        
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500">Time</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedDraw->draw_time }}</dd>
                                        </div>
                                        
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500">Result</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                @if($selectedDraw->result)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        {{ $selectedDraw->result }}
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                @endif
                                            </dd>
                                        </div>
                                        
                                        <div class="sm:col-span-1">
                                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                @if($selectedDraw->is_open)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Open
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Closed
                                                    </span>
                                                @endif
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                                
                                <!-- Draw Stats Summary -->
                                <div class="mt-6">
                                    <h4 class="text-base font-medium text-gray-900 mb-2">Summary</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
                                            <div class="sm:col-span-1">
                                                <dt class="text-sm font-medium text-gray-500">Total Bets</dt>
                                                <dd class="mt-1 text-lg font-semibold text-gray-900">{{ number_format($drawStats['total_bets']) }}</dd>
                                            </div>
                                            
                                            <div class="sm:col-span-1">
                                                <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                                                <dd class="mt-1 text-lg font-semibold text-gray-900">₱{{ number_format($drawStats['total_amount'], 2) }}</dd>
                                            </div>
                                            
                                            <div class="sm:col-span-1">
                                                <dt class="text-sm font-medium text-gray-500">Total Winning Amount</dt>
                                                <dd class="mt-1 text-lg font-semibold text-gray-900">₱{{ number_format($drawStats['total_winning_amount'], 2) }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                                
                                <!-- Game Type Breakdown -->
                                <div class="mt-6">
                                    <h4 class="text-base font-medium text-gray-900 mb-2">Game Type Breakdown</h4>
                                    <div class="bg-gray-50 rounded-lg overflow-hidden">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game Type</th>
                                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bets</th>
                                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Winning</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @forelse($drawStats['by_game_type'] as $gameType => $stats)
                                                    <tr>
                                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $gameType }}</td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ number_format($stats['count']) }}</td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($stats['amount'], 2) }}</td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($stats['winning_amount'], 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 text-center">No data available</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Teller Breakdown -->
                                <div class="mt-6">
                                    <h4 class="text-base font-medium text-gray-900 mb-2">Teller Breakdown</h4>
                                    <div class="bg-gray-50 rounded-lg overflow-hidden">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teller</th>
                                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bets</th>
                                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Winning</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @forelse($drawStats['by_teller'] as $tellerId => $stats)
                                                    <tr>
                                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $stats['name'] }}</td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ number_format($stats['count']) }}</td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($stats['amount'], 2) }}</td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($stats['winning_amount'], 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 text-center">No data available</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="$set('showDrawDetailsModal', false)" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
