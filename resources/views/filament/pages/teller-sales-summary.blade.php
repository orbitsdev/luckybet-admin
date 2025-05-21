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
    
    <div id="teller-sales-summary" class="p-6 space-y-4 w-full">
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
                <label for="selectedDrawId" class="text-sm font-medium">Select Draw:</label>
                <select wire:model.live="selectedDrawId" id="selectedDrawId" class="border rounded px-2 py-1 text-sm">
                    @foreach ($drawOptions as $draw)
                        <option value="{{ $draw['id'] }}">{{ $draw['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="report-content" class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 bg-amber-50 border-b border-amber-100">
                <h2 class="text-lg font-medium text-amber-800">
                    Sales Summary for {{ $drawOptions[array_search($selectedDrawId, array_column($drawOptions, 'id'))]['label'] ?? 'Selected Draw' }}
                </h2>
            </div>
            
            <table class="w-full divide-y divide-gray-200 border text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Draw Time</th>
                    <th class="px-4 py-2 text-left">Teller</th>
                    <th class="px-4 py-2 text-left">S2</th>
                    <th class="px-4 py-2 text-left">S3</th>
                    <th class="px-4 py-2 text-left">D4</th>
                    <th class="px-4 py-2 text-left">Total Sales</th>
                    <th class="px-4 py-2 text-left">Total Hits</th>
                    <th class="px-4 py-2 text-left">Gross</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->summary as $draw)
                    @if (!empty($draw['tellers']))
                        @foreach ($draw['tellers'] as $teller)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ \Carbon\Carbon::createFromFormat('H:i:s', $draw['draw_time'])->format('g:i A') }}</td>
                                <td class="px-4 py-2">{{ $teller['teller_name'] }}</td>
                                <td class="px-4 py-2">{{ $draw['s2_result'] ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $draw['s3_result'] ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $draw['d4_result'] ?? '-' }}</td>
                                <td class="px-4 py-2 font-medium">₱{{ number_format($teller['total_sales'], 2) }}</td>
                                <td class="px-4 py-2 font-medium">₱{{ number_format($teller['total_hits'], 2) }}</td>
                                <td class="px-4 py-2 font-semibold text-blue-600">₱{{ number_format($teller['gross'], 2) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="text-center py-4">No teller records for this draw.</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">No records found for selected date.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</x-filament-panels::page>
