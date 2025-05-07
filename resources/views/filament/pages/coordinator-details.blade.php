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
        <!-- Print Button and Back Button -->
        <div class="flex justify-between mb-4">
            <div>
                {{ $this->backAction }}
            </div>
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

        <!-- Search Box -->
        <div class="flex justify-end">
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
                    placeholder="Search teller name..."
                />
            </div>
        </div>

        <div id="report-content" class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 bg-amber-50 border-b border-amber-100">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-amber-800">{{ $this->coordinator->name }} - <span class="text-amber-600">Coordinator</span></h2>
                        <p class="text-sm text-amber-700">{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 grid grid-cols-3 gap-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-500">Total Sales:</p>
                    <p class="text-xl font-bold text-gray-800">₱{{ number_format($this->tellers->sum('total_sales'), 2) }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-500">Total Hits:</p>
                    <p class="text-xl font-bold text-gray-800">₱{{ number_format($this->tellers->sum('total_hits'), 2) }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-500">Total Gross:</p>
                    <p class="text-xl font-bold text-gray-800">₱{{ number_format($this->tellers->sum('total_gross'), 2) }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 border text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Teller's Name</th>
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
                        
                        @forelse ($this->tellers as $teller)
                            @php
                                $totalSales += $teller['total_sales'];
                                $totalHits += $teller['total_hits'];
                                $totalGross += $teller['total_gross'];
                            @endphp
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium">{{ $teller['name'] }}</td>
                                <td class="px-4 py-2">₱{{ number_format($teller['total_sales'], 2) }}</td>
                                <td class="px-4 py-2">₱{{ number_format($teller['total_hits'], 2) }}</td>
                                <td class="px-4 py-2">₱{{ number_format($teller['total_gross'], 2) }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex space-x-2">
                                        <a href="#" class="px-3 py-1 bg-blue-500 text-white rounded text-xs">View Tally Sheet</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No tellers found for this coordinator.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 font-medium">
                        <tr>
                            <td class="px-4 py-2 text-right">Totals:</td>
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
                                <span class="font-medium">{{ $this->tellers->firstItem() ?? 0 }}</span>
                                to
                                <span class="font-medium">{{ $this->tellers->lastItem() ?? 0 }}</span>
                                of
                                <span class="font-medium">{{ $this->tellers->total() }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <!-- Previous Page Link -->
                                <button
                                    wire:click="$set('page', {{ max($this->tellers->currentPage() - 1, 1) }})"
                                    @if ($this->tellers->onFirstPage()) disabled @endif
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                >
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <!-- Page Number Links -->
                                @for ($i = 1; $i <= $this->tellers->lastPage(); $i++)
                                    <button
                                        wire:click="$set('page', {{ $i }})"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium {{ $i === $this->tellers->currentPage() ? 'text-amber-600 bg-amber-50' : 'text-gray-700 hover:bg-gray-50' }}"
                                    >
                                        {{ $i }}
                                    </button>
                                @endfor

                                <!-- Next Page Link -->
                                <button
                                    wire:click="$set('page', {{ min($this->tellers->currentPage() + 1, $this->tellers->lastPage()) }})"
                                    @if (!$this->tellers->hasMorePages()) disabled @endif
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
