<div>
   <x-admin>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Winning Report</h1>
                <div class="flex items-center space-x-2">
                    <button wire:click="resetFilters" class="inline-flex items-center px-3 py-1 border border-gray-300 text-xs font-medium rounded-md shadow-sm text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset
                    </button>
                    
                    <button wire:click="loadData" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" wire:loading.class.remove="animate-none" wire:loading.class="animate-spin">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span wire:loading.remove>Refresh</span>
                        <span wire:loading>Refreshing...</span>
                    </button>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                <!-- Filters Section -->
                <div class="bg-white shadow rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Date Filter -->
                        <div>
                            <label for="selectedDate" class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" wire:model.live="selectedDate" id="selectedDate"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- Search -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search (Ticket ID/Bet Number)</label>
                            <input type="text" wire:model.live.debounce.300ms="search" id="search"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Search...">
                        </div>

                        <!-- Per Page -->
                        <div>
                            <label for="perPage" class="block text-sm font-medium text-gray-700">Items Per Page</label>
                            <select wire:model.live="perPage" id="perPage"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                        <!-- Game Type Filter -->
                        <div>
                            <label for="selectedGameType" class="block text-sm font-medium text-gray-700">Game Type</label>
                            <select wire:model.live="selectedGameType" id="selectedGameType"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Bet Types</option>
                                @foreach($gameTypes as $gameType)
                                    <option value="{{ $gameType->id }}">{{ $gameType->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Location Filter -->
                        <div>
                            <label for="selectedLocation" class="block text-sm font-medium text-gray-700">Location</label>
                            <select wire:model.live="selectedLocation" id="selectedLocation"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Locations</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Coordinator Filter -->
                        <div>
                            <label for="selectedCoordinator" class="block text-sm font-medium text-gray-700">Coordinator</label>
                            <select wire:model.live="selectedCoordinator" id="selectedCoordinator"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Coordinators</option>
                                @foreach($coordinators as $coordinator)
                                    <option value="{{ $coordinator->id }}">{{ $coordinator->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Teller Filter -->
                        <div>
                            <label for="selectedTeller" class="block text-sm font-medium text-gray-700">Teller</label>
                            <select wire:model.live="selectedTeller" id="selectedTeller"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Tellers</option>
                                @foreach($tellers as $teller)
                                    <option value="{{ $teller->id }}">{{ $teller->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <!-- D4 Sub-selection Filter (always visible) -->
                        <div>
                            <label for="selectedD4SubSelection" class="block text-sm font-medium text-gray-700">D4 Sub-selection</label>
                            <select wire:model.live="selectedD4SubSelection" id="selectedD4SubSelection"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All</option>
                                @foreach($d4SubSelections as $selection)
                                    <option value="{{ $selection }}">D4-{{ $selection }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Claimed Status Filter -->
                        <div>
                            <label for="selectedClaimedStatus" class="block text-sm font-medium text-gray-700">Claimed Status</label>
                            <select wire:model.live="selectedClaimedStatus" id="selectedClaimedStatus"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All</option>
                                <option value="1">Claimed</option>
                                <option value="0">Unclaimed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Buttons moved to top right corner -->
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Total Winners Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Winners</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($totalWinners) }}</dd>
                        </div>
                    </div>

                    <!-- Total Winning Amount Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Winning Amount</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">₱{{ number_format($totalWinAmount, 2) }}</dd>
                        </div>
                    </div>

                    <!-- Winners by Game Type Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Winners by Game Type</dt>
                            <dd class="mt-1">
                                @foreach($winnersByGameType as $gameTypeData)
                                    <div class="flex justify-between text-sm">
                                        <span>{{ $gameTypeData['name'] }}:</span>
                                        <span class="font-medium">{{ number_format($gameTypeData['count']) }}</span>
                                    </div>
                                @endforeach
                            </dd>
                        </div>
                    </div>

                    <!-- Winners by Location Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Winners by Location</dt>
                            <dd class="mt-1">
                                @foreach($winnersByLocation as $locationData)
                                    <div class="flex justify-between text-sm">
                                        <span>{{ $locationData['name'] }}:</span>
                                        <span class="font-medium">{{ number_format($locationData['count']) }}</span>
                                    </div>
                                @endforeach
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- Winners Table -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">D4 Sub</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bet Number</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bet Amount</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Winning Amount</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teller</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bet Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($winners as $bet)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $bet->ticket_id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                {{ $bet->gameType->name === 'S2' ? 'bg-green-100 text-green-800' :
                                                   ($bet->gameType->name === 'S3' ? 'bg-yellow-100 text-yellow-800' :
                                                   'bg-red-100 text-red-800') }}">
                                                {{ $bet->gameType->name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($bet->gameType->name === 'D4' && $bet->d4_sub_selection)
                                                {{ $bet->d4_sub_selection }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $bet->bet_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($bet->amount, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($bet->winning_amount, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $bet->teller->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $bet->location->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $bet->bet_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                {{ $bet->is_claimed ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $bet->is_claimed ? 'Claimed' : 'Unclaimed' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No winning bets found for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                        {{ $winners->links() }}
                    </div>
                </div>
            </div>
        </div>
   </x-admin>

   @push('scripts')
   <script>
       document.addEventListener('livewire:initialized', () => {
           @this.on('downloadFile', (data) => {
               const link = document.createElement('a');
               link.href = data.url;
               link.download = '';
               document.body.appendChild(link);
               link.click();
               document.body.removeChild(link);
           });
       });
   </script>
   @endpush
</div>
