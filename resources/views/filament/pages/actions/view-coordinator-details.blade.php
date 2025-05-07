<div class="p-6 space-y-4 w-full">
    <!-- Header with Coordinator Info -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 bg-amber-50 border-b border-amber-100">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-amber-800">{{ $coordinator->name }} - <span class="text-amber-600">Coordinator</span></h2>
                    <p class="text-sm text-amber-700">{{ $date }}</p>
                </div>
                <button 
                    onclick="printCoordinatorDetails()" 
                    class="px-4 py-2 bg-amber-500 text-white rounded-lg flex items-center space-x-2 hover:bg-amber-600 transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Print Details</span>
                </button>
            </div>
        </div>

        <div id="coordinator-details-content">
            <!-- Summary Cards -->
            <div class="p-4 grid grid-cols-3 gap-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-500">Total Sales:</p>
                    <p class="text-xl font-bold text-gray-800">₱{{ number_format($totalSales, 2) }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-500">Total Hits:</p>
                    <p class="text-xl font-bold text-gray-800">₱{{ number_format($totalHits, 2) }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-500">Total Gross:</p>
                    <p class="text-xl font-bold text-gray-800">₱{{ number_format($totalGross, 2) }}</p>
                </div>
            </div>

            <!-- Tellers Table -->
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 border text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Teller's Name</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Total Sales</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Total Hits</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Total Gross</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600 print:hidden">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tellers as $teller)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium">{{ $teller['name'] }}</td>
                                <td class="px-4 py-2">₱{{ number_format($teller['total_sales'], 2) }}</td>
                                <td class="px-4 py-2">₱{{ number_format($teller['total_hits'], 2) }}</td>
                                <td class="px-4 py-2">₱{{ number_format($teller['total_gross'], 2) }}</td>
                                <td class="px-4 py-2 print:hidden">
                                    <div class="flex space-x-2">
                                        <a href="/admin/teller-sales-summary?id={{ $teller['id'] }}" class="px-3 py-1 bg-blue-500 text-white rounded text-xs">View Tally Sheet</a>
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
                            <td class="px-4 py-2 print:hidden"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function printCoordinatorDetails() {
        // Create a new window
        var printWindow = window.open('', '_blank');
        
        // Get the content to print
        var content = document.getElementById('coordinator-details-content').cloneNode(true);
        
        // Remove elements with print:hidden class
        var hiddenElements = content.querySelectorAll('.print\\:hidden');
        hiddenElements.forEach(function(el) {
            el.style.display = 'none';
        });
        
        // Add styles
        var styles = `
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
                th { background-color: #f2f2f2; }
                .bg-gray-50 { background-color: #f9fafb; }
                .rounded-lg { border-radius: 0.5rem; }
                .p-3 { padding: 0.75rem; }
                .p-4 { padding: 1rem; }
                .grid { display: grid; }
                .grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
                .gap-4 { gap: 1rem; }
                .text-sm { font-size: 0.875rem; }
                .text-xl { font-size: 1.25rem; }
                .font-bold { font-weight: bold; }
                .text-gray-500 { color: #6b7280; }
                .text-gray-800 { color: #1f2937; }
                @media print {
                    .print\\:hidden { display: none !important; }
                }
            </style>
        `;
        
        // Write to the new window
        printWindow.document.write(`
            <html>
                <head>
                    <title>Coordinator Details: ${document.title}</title>
                    ${styles}
                </head>
                <body>
                    <h1 style="text-align: center; margin-bottom: 20px;">Coordinator Details: ${document.title}</h1>
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
