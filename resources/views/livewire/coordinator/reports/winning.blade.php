<div>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl font-bold">Winning Report</h1>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 bg-white rounded-lg shadow-md p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Date Range Filter -->
                <div>
                    <label for="dateRange" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <select wire:model.live="dateRange" id="dateRange" class="form-select w-full">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="this_week">This Week</option>
                        <option value="last_week">Last Week</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>

                <!-- Custom Date Range -->
                @if ($dateRange === 'custom')
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" wire:model.live="startDate" id="startDate" class="form-input w-full">
                </div>
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" wire:model.live="endDate" id="endDate" class="form-input w-full">
                </div>
                @endif

                <!-- Draw Filter -->
                <div>
                    <label for="selectedDraw" class="block text-sm font-medium text-gray-700 mb-1">Draw</label>
                    <select wire:model.live="selectedDraw" id="selectedDraw" class="form-select w-full">
                        <option value="">All Draws</option>
                        @foreach ($draws as $draw)
                            <option value="{{ $draw->id }}">{{ $draw->name }} ({{ $draw->time }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Button -->
                <div class="flex items-end">
                    <button wire:click="applyFilters" class="btn bg-red-500 hover:bg-red-600 text-white">
                        <span class="hidden xs:block ml-2">Search</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Bets Card -->
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Bets</p>
                        <p class="text-2xl font-bold">{{ number_format($totalBets) }}</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-red-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Sales Card -->
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Sales</p>
                        <p class="text-2xl font-bold">₱{{ number_format($totalSales, 2) }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-green-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Winning Bets Card -->
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Winning Bets</p>
                        <p class="text-2xl font-bold">{{ number_format($winningBets) }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-blue-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Payouts Card -->
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Payouts</p>
                        <p class="text-2xl font-bold">₱{{ number_format($totalPayouts, 2) }}</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-yellow-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Winning Numbers Table -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Winning Numbers</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Draw</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Winning Number</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Bets</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Winning Bets</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Payouts</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($winningNumbers as $winningNumber)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $winningNumber->draw->name }} ({{ $winningNumber->draw->time }})</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $winningNumber->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">{{ $winningNumber->number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($winningNumber->total_bets) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($winningNumber->winning_bets) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($winningNumber->total_sales, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($winningNumber->total_payouts, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No winning numbers found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $winningNumbers->links() }}
            </div>
        </div>

        <!-- Winning Bets Table -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Winning Bets</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teller</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Draw</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payout</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($winningBetsList as $bet)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bet->ticket_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bet->teller->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bet->draw->name }} ({{ $bet->draw->time }})</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $bet->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">{{ $bet->number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($bet->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($bet->payout, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No winning bets found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $winningBetsList->links() }}
            </div>
        </div>
    </div>
</div>
