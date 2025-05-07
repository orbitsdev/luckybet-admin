<div id="coordinator-report" class="p-6 space-y-4 w-full">
    <!-- Print Button - Positioned at the top right -->
    <div class="flex justify-end mb-4">
        <button
            onclick="printCoordinatorReport()"
            class="print-report-button"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Report
        </button>
    </div>

    <div id="coordinator-report-content" class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 bg-amber-50 border-b border-amber-100">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    {{-- <img src="{{ asset('assets/logo.png')}}" alt="Lucky Bet Logo" class="h-12"> --}}
                    <div>
                        <h2 class="text-xl font-bold text-amber-800">COORDINATORS SALES SUMMARY REPORT</h2>
                        <p class="text-sm text-amber-700">{{ $date ? \Carbon\Carbon::parse($date)->format('F j, Y') : 'All Dates' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 border text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Coordinator's Name</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Total Sales</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Total Hits</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Total Gross</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // This is a placeholder - you'll need to replace this with your actual data
                        $coordinators = \App\Models\User::where('role', 'coordinator')
                            ->when($coordinatorId, function($query) use ($coordinatorId) {
                                return $query->where('id', $coordinatorId);
                            })
                            ->get();

                        $totalSales = 0;
                        $totalHits = 0;
                        $totalGross = 0;
                    @endphp

                    @forelse ($coordinators as $coordinator)
                        @php
                            // Calculate sales for this coordinator
                            // This is a placeholder - replace with your actual calculation logic
                            $sales = 12000.00;
                            $hits = 1000.00;
                            $gross = 11000.00;

                            $totalSales += $sales;
                            $totalHits += $hits;
                            $totalGross += $gross;
                        @endphp
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium">{{ $coordinator->name }}</td>
                            <td class="px-4 py-2">₱{{ number_format($coordinator->total_sales ?? 0, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($coordinator->total_hits ?? 0, 2) }}</td>
                            <td class="px-4 py-2">₱{{ number_format($coordinator->total_gross ?? 0, 2) }}</td>
                            <td class="px-4 py-2">
                                <div class="flex space-x-2">
                                    <a href="#" style="margin: 10px;  " class=" bg-amber-500 text-white rounded text-xs">Coordinator Sheet</a>
                                    <a href="#" style="margin: 10px;  " class=" bg-blue-500 text-white rounded text-xs">Teller Sheet</a>
                                    <a href="#" style="margin: 10px;  " class=" bg-green-500 text-white rounded text-xs">Tally Sheet</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No coordinators found or update backend to provide real data.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-medium">
                    <tr>
                        <td class="px-4 py-2">Total Sales: {{ number_format($totalSales, 2) }}</td>
                        <td class="px-4 py-2">Total Hits: {{ number_format($totalHits, 2) }}</td>
                        <td class="px-4 py-2">Total Gross: {{ number_format($totalGross, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
    function printCoordinatorReport() {
        // Clone the report content
        var reportDiv = document.getElementById('coordinator-report-content').cloneNode(true);

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
