<div>
    <x-admin>
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Teller Sales Report</h1>
                <p class="text-gray-600">View sales data for all tellers</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <button wire:click="toggleFilters" class="btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                    </svg>
                    {{ $showFilters ? 'Hide Filters' : 'Show Filters' }}
                </button>
                <button wire:click="prepareToPrint" onclick="window.print()" class="btn-primary print:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                    </svg>
                    Print Report
                </button>
            </div>
        </div>

        <!-- Date display for print -->
        <div class="hidden print:block mb-4">
            <p class="text-gray-600">Report Date: {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</p>
        </div>

        <!-- Filters -->
        <div x-data="{ open: @entangle('showFilters') }" x-show="open" x-transition class="bg-white rounded-lg shadow-md p-4 mb-6 print:hidden">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" wire:model.live="date" id="date" class="form-input w-full rounded-md">
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <select wire:model.live="location_id" id="location" class="form-select w-full rounded-md">
                        <option value="">All Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="coordinator" class="block text-sm font-medium text-gray-700 mb-1">Coordinator</label>
                    <select wire:model.live="coordinator_id" id="coordinator" class="form-select w-full rounded-md">
                        <option value="">All Coordinators</option>
                        @foreach($coordinators as $coordinator)
                            <option value="{{ $coordinator->id }}">{{ $coordinator->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="search" id="search" placeholder="Search tellers..." class="form-input w-full rounded-md pl-10">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button wire:click="resetFilters" class="btn-secondary">Reset Filters</button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="mb-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-white p-3 rounded-md shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Total Sales</h3>
                        <p class="text-2xl font-bold {{ $tellers->sum('total_sales') >= 0 ? 'text-green-600' : 'text-red-600' }}">₱{{ number_format($tellers->sum('total_sales'), 2) }}</p>
                    </div>
                    <div class="bg-white p-3 rounded-md shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Total Hits</h3>
                        <p class="text-2xl font-bold {{ $tellers->sum('total_hits') >= 0 ? 'text-green-600' : 'text-red-600' }}">₱{{ number_format($tellers->sum('total_hits'), 2) }}</p>
                    </div>
                    <div class="bg-white p-3 rounded-md shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Total Commission</h3>
                        <p class="text-2xl font-bold {{ $tellers->sum('total_commission') >= 0 ? 'text-green-600' : 'text-red-600' }}">₱{{ number_format($tellers->sum('total_commission'), 2) }}</p>
                    </div>
                    <div class="bg-white p-3 rounded-md shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Total Gross</h3>
                        <p class="text-2xl font-bold {{ ($tellers->sum('total_sales') - $tellers->sum('total_hits') - $tellers->sum('total_commission')) >= 0 ? 'text-green-600' : 'text-red-600' }}">₱{{ number_format($tellers->sum('total_sales') - $tellers->sum('total_hits') - $tellers->sum('total_commission'), 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tellers Table -->
        <div class="mb-8 bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teller</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coordinator</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hits</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:hidden">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tellers as $teller)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $teller->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $teller->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $teller->location->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $teller->coordinator->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ ($teller->total_sales ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    ₱{{ number_format($teller->total_sales ?? 0, 2, '.', ',') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ ($teller->total_hits ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    ₱{{ number_format($teller->total_hits ?? 0, 2, '.', ',') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ ($teller->total_commission ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    ₱{{ number_format($teller->total_commission ?? 0, 2, '.', ',') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ (($teller->total_sales ?? 0) - ($teller->total_hits ?? 0) - ($teller->total_commission ?? 0)) >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    ₱{{ number_format(($teller->total_sales ?? 0) - ($teller->total_hits ?? 0) - ($teller->total_commission ?? 0), 2, '.', ',') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm print:hidden">
                                    <div class="flex space-x-2">
                                        {{ ($this->viewTellerDetailsAction)(['teller_id' => $teller->id]) }}
                                        {{ ($this->viewTellerBetsAction)(['teller_id' => $teller->id]) }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No tellers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4 print:hidden">
            {{ $tellers->links() }}
        </div>
    </div>
    <x-filament-actions::modals />
</x-admin>
</div>
