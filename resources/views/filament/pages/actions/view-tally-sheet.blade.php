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
                    class="px-4 py-2 bg-amber-500 text-white rounded-lg flex items-center space-x-2 hover:bg-amber-600 transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Print Tally Sheet</span>
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
        // Create a new window
        var printWindow = window.open('', '_blank');
        
        // Get the content to print
        var content = document.getElementById('tally-sheet-content').cloneNode(true);
        
        // Add styles
        var styles = `
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
                th { background-color: #f2f2f2; }
                .text-amber-600 { color: #d97706; }
                .font-medium { font-weight: 500; }
                @media print {
                    .print\\:hidden { display: none !important; }
                }
            </style>
        `;
        
        // Write to the new window
        printWindow.document.write(`
            <html>
                <head>
                    <title>Tally Sheet: ${document.title}</title>
                    ${styles}
                </head>
                <body>
                    <h1 style="text-align: center; margin-bottom: 20px;">Tally Sheet: ${document.title}</h1>
                    ${content.outerHTML}
                </body>
            </html>
        `);
        
        // Finish writing and trigger print
        printWindow.document.close();
        printWindow.focus();
        
        // Wait for content to load before printing
        printWindow.onload = function() {
            printWindow.print();
            // printWindow.close();
        };
    }
</script>
