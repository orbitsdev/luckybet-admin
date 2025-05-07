<div class="p-6 space-y-4 w-full">
    <!-- Header with Coordinator Info -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 bg-amber-50 border-b border-amber-100">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-amber-800">{{ $coordinator->name }} - <span class="text-amber-600">Tally Sheet</span></h2>
                    <p class="text-sm text-amber-700">{{ $date }}</p>
                </div>
                <button 
                    onclick="printTallySheet()" 
                    class="print-report-button"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Tally Sheet
                </button>
            </div>
        </div>

        <div id="tally-sheet-content">
            <!-- Draws Table -->
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 border text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Draw Time</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">S2 Winning Number</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">S3 Winning Number</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">D4 Winning Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($draws as $draw)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium">{{ $draw['time'] }}</td>
                                <td class="px-4 py-2 font-medium text-amber-600">{{ $draw['s2'] }}</td>
                                <td class="px-4 py-2 font-medium text-amber-600">{{ $draw['s3'] }}</td>
                                <td class="px-4 py-2 font-medium text-amber-600">{{ $draw['d4'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">No draw results found for this date.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function printTallySheet() {
        // Clone the report content
        var reportDiv = document.getElementById('tally-sheet-content').cloneNode(true);
        
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = reportDiv.innerHTML;
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>
