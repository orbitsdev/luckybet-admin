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

    <div class="p-6 space-y-4 w-full">
        <!-- Print Button - Positioned at the top right -->
        <div class="flex justify-end mb-4">
            <button
                onclick="printDiv('report-content')"
                class="print-report-button"
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
                    class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-amber-500 focus:border-amber-500"
                    placeholder="Search coordinator name..."
                />
            </div>
        </div>

        <div id="report-content" class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 bg-amber-50 border-b border-amber-100">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        {{-- <img src="{{ asset('assets/logo.png') }}" alt="Lucky Bet Logo" class="h-12"> --}}
                        <div>
                            <h2 class="text-xl font-bold text-amber-800">COORDINATORS SALES SUMMARY REPORT</h2>
                            <p class="text-sm text-amber-700">{{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 border text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Coordinator's Name</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Tellers</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Total Sales</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Total Hits</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Total Gross</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalSales = 0;
                            $totalHits = 0;
                            $totalGross = 0;
                        @endphp

                        @forelse ($this->coordinators as $coordinator)
                            @php
                                $totalSales += $coordinator['total_sales'];
                                $totalHits += $coordinator['total_hits'];
                                $totalGross += $coordinator['total_gross'];
                            @endphp
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium">{{ $coordinator['name'] }}</td>
                                <td class="px-4 py-2">{{ $coordinator['teller_count'] }}</td>
                                <td class="px-4 py-2">₱{{ number_format($coordinator['total_sales'], 2) }}</td>
                                <td class="px-4 py-2">₱{{ number_format($coordinator['total_hits'], 2) }}</td>
                                <td class="px-4 py-2">₱{{ number_format($coordinator['total_gross'], 2) }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex space-x-2">
                                        <a href="/admin/coordinator-details?coordinatorId={{ $coordinator['id'] }}&date={{ $selectedDate }}" class="px-3 py-1 bg-amber-500 text-white rounded text-xs">Coordinator Sheet</a>
                                        <a href="/admin/teller-sales-summary?coordinatorId={{ $coordinator['id'] }}&date={{ $selectedDate }}" class="px-3 py-1 bg-blue-500 text-white rounded text-xs">Teller Sheet</a>
                                        <a href="/admin/coordinator-details?coordinatorId={{ $coordinator['id'] }}&date={{ $selectedDate }}&view=tally" class="px-3 py-1 bg-green-500 text-white rounded text-xs">Tally Sheet</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No coordinators found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 font-medium">
                        <tr>
                            <td colspan="2" class="px-4 py-2 text-right">Totals:</td>
                            <td class="px-4 py-2">₱{{ number_format($totalSales, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($totalHits, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($totalGross, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Pagination Controls - Hidden when printing -->
            <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6 print:hidden">
                <div class="flex items-center justify-between">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ $this->coordinators->firstItem() ?? 0 }}</span>
                                to
                                <span class="font-medium">{{ $this->coordinators->lastItem() ?? 0 }}</span>
                                of
                                <span class="font-medium">{{ $this->coordinators->total() }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <!-- Previous Page Link -->
                                <button
                                    wire:click="$set('page', {{ max($this->coordinators->currentPage() - 1, 1) }})"
                                    @if ($this->coordinators->onFirstPage()) disabled @endif
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                >
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <!-- Page Number Links -->
                                @for ($i = 1; $i <= $this->coordinators->lastPage(); $i++)
                                    <button
                                        wire:click="$set('page', {{ $i }})"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium {{ $i === $this->coordinators->currentPage() ? 'text-amber-600 bg-amber-50' : 'text-gray-700 hover:bg-gray-50' }}"
                                    >
                                        {{ $i }}
                                    </button>
                                @endfor

                                <!-- Next Page Link -->
                                <button
                                    wire:click="$set('page', {{ min($this->coordinators->currentPage() + 1, $this->coordinators->lastPage()) }})"
                                    @if (!$this->coordinators->hasMorePages()) disabled @endif
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
</x-filament-panels::page>
