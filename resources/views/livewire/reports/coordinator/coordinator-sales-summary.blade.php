<div>
    <x-admin>
    <div class="p-4 bg-white rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Coordinator Sales Summary</h1>
            <div class="flex space-x-2">
                <div class="flex items-center space-x-2">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" wire:model.live="date" id="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="searchTerm" class="block text-sm font-medium text-gray-700">Search Coordinator</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" wire:model.live.debounce.300ms="searchTerm" id="searchTerm" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Enter coordinator name">
                            @if(!empty($searchTerm))
                                <button wire:click="resetSearch" class="ml-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="mt-6">
                        <button wire:click="resetFilters" class="inline-flex items-center px-3 py-1.5 bg-gray-100 rounded-md font-medium text-sm text-gray-700 hover:bg-gray-200 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset All Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white p-3 rounded-md shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Total Sales</h3>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($totalSales, 2) }}</p>
                    </div>
                    <div class="bg-white p-3 rounded-md shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Total Hits</h3>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($totalHits, 2) }}</p>
                    </div>
                    <div class="bg-white p-3 rounded-md shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Total Gross</h3>
                        <p class="text-2xl font-bold {{ $totalGross >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($totalGross, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mb-2 text-sm text-gray-600">
            <span class="font-medium">Date:</span> {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}
            @if($searchTerm)
                <span class="ml-4 font-medium">Filter:</span> {{ $searchTerm }}
            @endif
        </div>
        
        @if(count($salesData) > 0)
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hits</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($salesData as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-bold">{{ number_format($item['total_sales'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-bold">{{ number_format($item['total_hits'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $item['total_gross'] >= 0 ? 'text-green-600' : 'text-red-600' }} font-bold">{{ number_format($item['total_gross'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center space-x-3">
                                        <a target="_blank" href="{{ route('reports.teller-sales-summary', ['coordinator_id' => $item['id']]) }}" class="inline-flex items-center px-2 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            View Details
                                        </a>
                                        <div>
                                            {{ ($this->viewCoordinatorSummaryAction)(['coordinator_id' => $item['id']]) }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded relative" role="alert">
                <p>No sales data found for the selected criteria.</p>
            </div>
        @endif
    </div>
    
    <x-filament-actions::modals />
</x-admin>
</div>
