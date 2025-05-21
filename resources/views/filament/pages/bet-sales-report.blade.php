<x-filament-panels::page>
    <!-- Print script -->
    <script>
        function printDiv(divName) {
            // Clone the report content
            var reportDiv = document.getElementById(divName).cloneNode(true);

            // Remove pagination elements
            var paginationElements = reportDiv.querySelectorAll('.print\\:hidden');
            paginationElements.forEach(function(el) {
                el.parentNode.removeChild(el);
            });

            var originalContents = document.body.innerHTML;
            document.body.innerHTML = reportDiv.innerHTML;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>

    <div id="bet-sales-report" class="p-6 space-y-4 w-full">
        <!-- Print Button - Positioned at the top right -->
        <div class="flex justify-end mb-4">
            <button
                onclick="printDiv('report-content')"
                class="filament-button"
                style="background-color: #f59e0b; color: white; font-weight: bold; padding: 0.75rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); display: inline-flex; align-items: center; gap: 0.5rem; font-size: 1rem;"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Report
            </button>
        </div>

        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center space-x-2">
                <label for="selectedDate" class="text-sm font-medium">Select Date:</label>
                <select wire:model.live="selectedDate" id="selectedDate" class="border rounded px-2 py-1 text-sm">
                    @foreach ($dateOptions as $option)
                        <option value="{{ $option['date'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <label for="selectedTellerId" class="text-sm font-medium">Teller:</label>
                <select wire:model.live="selectedTellerId" id="selectedTellerId" class="border rounded px-2 py-1 text-sm">
                    <option value="all">All Tellers</option>
                    @foreach ($tellerOptions as $teller)
                        <option value="{{ $teller['id'] }}">{{ $teller['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search Box -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Search ticket ID, bet number..."
                />
            </div>
        </div>

        <div id="report-content" class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 bg-amber-50 border-b border-amber-100">
                <h2 class="text-lg font-medium text-amber-800">
                    Bet Sales for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}
                    @if($selectedTellerId !== 'all')
                        - {{ collect($tellerOptions)->firstWhere('id', $selectedTellerId)['name'] ?? '' }}
                    @endif
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 border text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Ticket ID</th>
                            <th class="px-4 py-2 text-left">Draw Date & Time</th>
                            <th class="px-4 py-2 text-left">Game Type</th>
                            <th class="px-4 py-2 text-left">Bet Number</th>
                            <th class="px-4 py-2 text-left">Amount</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Teller</th>
                            <th class="px-4 py-2 text-left">Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->bets as $bet)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium">{{ $bet['ticket_id'] }}</td>
                                <td class="px-4 py-2">{{ $bet['draw_date'] }} at {{ $bet['draw_time'] }}</td>
                                <td class="px-4 py-2">{{ $bet['game_type'] }}</td>
                                <td class="px-4 py-2">{{ $bet['bet_number'] }}</td>
                                <td class="px-4 py-2">₱{{ number_format($bet['amount'], 2) }}</td>
                                <td class="px-4 py-2">
                                    @if($bet['status'] === 'Active')
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Active</span>
                                    @elseif($bet['status'] === 'Cancelled')
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Cancelled</span>
                                    @elseif($bet['status'] === 'Won')
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Won</span>
                                    @elseif($bet['status'] === 'Claimed')
                                        <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">Claimed</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2">{{ $bet['teller_name'] }}</td>
                                <td class="px-4 py-2">{{ $bet['location_name'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">No bets found for selected date.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination Controls - Hidden when printing -->
                <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6 print:hidden">
                    <div class="flex items-center justify-between">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{ $this->bets->firstItem() ?? 0 }}</span>
                                    to
                                    <span class="font-medium">{{ $this->bets->lastItem() ?? 0 }}</span>
                                    of
                                    <span class="font-medium">{{ $this->bets->total() }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <!-- Previous Page Link -->
                                    <button
                                        wire:click="$set('page', {{ max($this->bets->currentPage() - 1, 1) }})"
                                        @if ($this->bets->onFirstPage()) disabled @endif
                                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                    >
                                        <span class="sr-only">Previous</span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <!-- Page Number Links -->
                                    @for ($i = 1; $i <= $this->bets->lastPage(); $i++)
                                        <button
                                            wire:click="$set('page', {{ $i }})"
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium {{ $i === $this->bets->currentPage() ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50' }}"
                                        >
                                            {{ $i }}
                                        </button>
                                    @endfor

                                    <!-- Next Page Link -->
                                    <button
                                        wire:click="$set('page', {{ min($this->bets->currentPage() + 1, $this->bets->lastPage()) }})"
                                        @if (!$this->bets->hasMorePages()) disabled @endif
                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                    >
                                        <span class="sr-only">Next</span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
