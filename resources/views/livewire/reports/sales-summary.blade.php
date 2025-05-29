<div>
    <x-admin>
        <div class="p-4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="p-4 sm:p-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Lucky Bet Sales Summary Report</h1>
                        <p class="text-sm text-gray-500">Generate a sales summary report for all coordinators</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <div class="w-full sm:w-auto">
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Report Date</label>
                            <input 
                                type="date" 
                                id="date" 
                                wire:model.live="selectedDate" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>
                    
                        <div class="w-full sm:w-auto self-end">
                            <button 
                                wire:click="generateReport" 
                                wire:loading.attr="disabled" 
                                class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="generateReport">Generate Report</span>
                                <span wire:loading wire:target="generateReport">Processing...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        
            @if($isGenerating)
                <div class="flex justify-center items-center p-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>
            @elseif(count($reportData) > 0)
                <!-- Summary Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Total Sales</div>
                                <div class="text-2xl font-semibold text-gray-900">{{ number_format($totalSales, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Total Hits</div>
                                <div class="text-2xl font-semibold text-gray-900">{{ number_format($totalHits, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Total Gross</div>
                                <div class="text-2xl font-semibold text-gray-900">{{ number_format($totalGross, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Report Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="p-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Coordinator Sales Summary</h2>
                            <button 
                                wire:click="generatePrintableReport" 
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Report
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coordinator's Name</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hits</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Gross</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData as $data)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $data['coordinator']->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-blue-600 font-medium">{{ number_format($data['total_sales'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600 font-medium">{{ number_format($data['total_hits'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600 font-medium">{{ number_format($data['total_gross'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <div class="flex justify-center space-x-2">
                                                    <a href="{{ route('reports.report-by-coordinator', ['coordinator' => $data['coordinator']->id, 'date' => $selectedDate]) }}" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        Coordinator Sheet
                                                    </a>
                                                    <a href="{{ route('reports.report-by-teller', ['coordinator' => $data['coordinator']->id, 'date' => $selectedDate]) }}" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                        Teller's Sheet
                                                    </a>
                                                    <button class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Tally Sheet
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TOTAL</th>
                                        <td class="px-6 py-3 text-right text-xs font-bold text-blue-600">{{ number_format($totalSales, 2) }}</td>
                                        <td class="px-6 py-3 text-right text-xs font-bold text-red-600">{{ number_format($totalHits, 2) }}</td>
                                        <td class="px-6 py-3 text-right text-xs font-bold text-green-600">{{ number_format($totalGross, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @elseif(!$isGenerating)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No report generated</h3>
                    <p class="mt-1 text-sm text-gray-500">Select a date and click 'Generate Report' to view the sales summary.</p>
                </div>
            @endif
            
            <!-- Printable Report Modal -->
            @if($printableReport)
                <div id="printable-report" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-900">Lucky Bet Summary Report</h2>
                            <div class="flex space-x-2">
                                <button 
                                    onclick="printDiv('print-content')" 
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    PRINT REPORT
                                </button>
                                <button 
                                    wire:click="$set('printableReport', null)" 
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    CANCEL
                                </button>
                            </div>
                        </div>
                        
                        <div id="print-content" class="p-6">
                            <!-- Report Header -->
                            <div class="text-center mb-6">
                                <div class="flex items-center justify-center mb-2">
                                    <img src="/images/logo.png" alt="Lucky Bet Logo" class="h-12 mr-2">
                                    <h1 class="text-2xl font-bold text-green-800">LUCKY BET</h1>
                                </div>
                                <h2 class="text-xl font-bold uppercase">COORDINATORS SALES SUMMARY REPORT</h2>
                                <p class="text-gray-600">{{ $printableReport['date'] }}</p>
                            </div>
                            
                            <!-- Report Table -->
                            <table class="min-w-full border border-gray-300 mb-6">
                                <thead>
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Coordinator's Name</th>
                                        <th class="border border-gray-300 px-4 py-2 text-right">Total Sales</th>
                                        <th class="border border-gray-300 px-4 py-2 text-right">Total Hits</th>
                                        <th class="border border-gray-300 px-4 py-2 text-right">Total Gross</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($printableReport['coordinators'] as $data)
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2">{{ $data['coordinator']->name }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right">{{ number_format($data['total_sales'], 2) }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right">{{ number_format($data['total_hits'], 2) }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right">{{ number_format($data['total_gross'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Total Sales: {{ number_format($printableReport['totals']['sales'], 2) }}</th>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Total Hits: {{ number_format($printableReport['totals']['hits'], 2) }}</th>
                                        <th class="border border-gray-300 px-4 py-2 text-left" colspan="2">Total Gross: {{ number_format($printableReport['totals']['gross'], 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                            
                            <!-- Signature Section -->
                            <div class="grid grid-cols-2 gap-8 mt-12">
                                <div class="text-center">
                                    <div class="border-t border-gray-400 pt-2">
                                        <p class="font-semibold">Prepared by:</p>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="border-t border-gray-400 pt-2">
                                        <p class="font-semibold">Approved by:</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Print Script -->
            <script>
                function printDiv(divId) {
                    const printContents = document.getElementById(divId).innerHTML;
                    const originalContents = document.body.innerHTML;
                    
                    document.body.innerHTML = `
                        <html>
                            <head>
                                <title>Lucky Bet Sales Summary Report</title>
                                <style>
                                    @media print {
                                        body { font-family: Arial, sans-serif; }
                                        table { width: 100%; border-collapse: collapse; }
                                        th, td { border: 1px solid #ddd; padding: 8px; }
                                        th { background-color: #f2f2f2; }
                                    }
                                </style>
                            </head>
                            <body>
                                ${printContents}
                            </body>
                        </html>
                    `;
                    
                    window.print();
                    document.body.innerHTML = originalContents;
                }
            </script>
        </div>
    </x-admin>
</div>