<div>
   <x-admin>
        <div>
            <!-- Header Section with Title and Action Buttons -->
            <div class="mx-auto px-4 sm:px-6 md:px-8 flex justify-between items-center mb-6">
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

            <!-- Main Content Area -->
            <div>
                <!-- Filters Section -->
                <div class="bg-white shadow rounded-lg p-4 mb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Filters</h2>
                    
                    <!-- Primary Filters Row -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <!-- Date Filter -->
                        <div>
                            <label for="selectedDate" class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" wire:model.live="selectedDate" id="selectedDate"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- Date Range placeholder (removed search) -->
                        <div></div>

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
                    
                    <!-- Divider -->
                    <div class="border-t border-gray-200 my-4"></div>
                    
                    <!-- Location & Personnel Filters -->
                    <div class="mb-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Location & Personnel</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    </div>
                    
                    <!-- Divider -->
                    <div class="border-t border-gray-200 my-4"></div>
                    
                    <!-- Bet Type Filters -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Bet Type & Status</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                            
                            <!-- D4 Sub-selection Filter -->
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
                    </div>
                </div>

                <!-- Statistics Section -->
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Summary Statistics</h2>
                    
                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Total Winners Card -->
                        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                            <div class="px-4 py-5 sm:p-6">
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Winners</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($totalWinners) }}</dd>
                            </div>
                        </div>

                        <!-- Total Winning Amount Card -->
                        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                            <div class="px-4 py-5 sm:p-6">
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Winning Amount</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900">₱{{ number_format($totalWinAmount, 2) }}</dd>
                            </div>
                        </div>

                        <!-- Winners by Game Type Card -->
                        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                            <div class="px-4 py-5 sm:p-6">
                                <dt class="text-sm font-medium text-gray-500 truncate">Winners by Bet Type</dt>
                                <dd class="mt-1 max-h-24 overflow-y-auto">
                                    @forelse($winnersByGameType as $gameTypeData)
                                        <div class="flex justify-between text-sm py-1">
                                            <span>{{ $gameTypeData['name'] }}:</span>
                                            <span class="font-medium">{{ number_format($gameTypeData['count']) }}</span>
                                        </div>
                                    @empty
                                        <div class="text-sm text-gray-500">No data available</div>
                                    @endforelse
                                </dd>
                            </div>
                        </div>

                        <!-- Winners by Location Card -->
                        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                            <div class="px-4 py-5 sm:p-6">
                                <dt class="text-sm font-medium text-gray-500 truncate">Winners by Location</dt>
                                <dd class="mt-1 max-h-24 overflow-y-auto">
                                    @forelse($winnersByLocation as $locationData)
                                        <div class="flex justify-between text-sm py-1">
                                            <span>{{ $locationData['name'] }}:</span>
                                            <span class="font-medium">{{ number_format($locationData['count']) }}</span>
                                        </div>
                                    @empty
                                        <div class="text-sm text-gray-500">No data available</div>
                                    @endforelse
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Winners Table Section -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-medium text-gray-900">Winning Bets</h2>
                        <button wire:click="exportToCsv" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export to CSV
                        </button>
                    </div>
                    
                    <!-- Search above the table -->
                    <div class="mb-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="search" id="search"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Search by Ticket ID or Bet Number...">
                        </div>
                    </div>
                    
                    <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-200">
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
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        {{ $bet->d4_sub_selection }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $bet->bet_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($bet->amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">₱{{ number_format($bet->winning_amount, 2) }}</td>
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
                                            <td colspan="10" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center">No winning bets found for the selected filters.</td>
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
