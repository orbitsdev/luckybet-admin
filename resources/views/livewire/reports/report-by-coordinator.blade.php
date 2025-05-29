<div>
    <x-admin>
        <div class="p-4">
            <!-- Filter Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col space-y-4">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Coordinator Report</h1>
                            <p class="text-sm text-gray-500">Generate a detailed report for a specific coordinator</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Coordinator Selection -->
                            <div>
                                <label for="coordinator" class="block text-sm font-medium text-gray-700 mb-1">Select Coordinator</label>
                                <select id="coordinator" wire:model.live="selectedCoordinator" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">-- Select Coordinator --</option>
                                    @foreach($coordinators as $coordinator)
                                        <option value="{{ $coordinator->id }}">{{ $coordinator->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date Selection -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Select Date</label>
                                <input type="date" id="date" wire:model.live="selectedDate" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            </div>

                            <!-- Draw Time Selection (Optional) -->
                            <div>
                                <label for="drawTime" class="block text-sm font-medium text-gray-700 mb-1">Draw Time (Optional)</label>
                                <select id="drawTime" wire:model.live="selectedDrawTime" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">-- All Draw Times --</option>
                                    @foreach($drawTimes as $drawTime)
                                        <option value="{{ $drawTime['id'] }}">{{ $drawTime['formatted_time'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button 
                                wire:click="generateReport" 
                                wire:loading.attr="disabled" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <span wire:loading.remove wire:target="generateReport">Generate Report</span>
                                <span wire:loading wire:target="generateReport">Generating...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Report Data Section -->
            @if(!empty($reportData))
                <!-- Summary Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <!-- Total Sales Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Sales</dt>
                                    <dd class="text-2xl font-semibold text-blue-600">{{ number_format($totalSales, 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Hits Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Hits</dt>
                                    <dd class="text-2xl font-semibold text-red-600">{{ number_format($totalHits, 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Gross Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Gross</dt>
                                    <dd class="text-2xl font-semibold text-green-600">{{ number_format($totalGross, 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Draw Time Report Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="p-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Draw Time Summary</h2>
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
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Draw Time</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hits</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gross</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData as $data)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $data['formatted_time'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-blue-600 font-medium">{{ number_format($data['sales'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600 font-medium">{{ number_format($data['hits'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600 font-medium">{{ number_format($data['gross'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TOTAL</th>
                                        <td class="px-6 py-3 text-right text-xs font-bold text-blue-600">{{ number_format($totalSales, 2) }}</td>
                                        <td class="px-6 py-3 text-right text-xs font-bold text-red-600">{{ number_format($totalHits, 2) }}</td>
                                        <td class="px-6 py-3 text-right text-xs font-bold text-green-600">{{ number_format($totalGross, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            
                <!-- Teller Report Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="p-4 sm:p-6">
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Teller Sales Summary</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teller's Name</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hits</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Gross</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($tellerReports as $index => $data)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $data['teller']->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-blue-600 font-medium">{{ number_format($data['total_sales'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600 font-medium">{{ number_format($data['total_hits'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600 font-medium">{{ number_format($data['total_gross'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <div class="flex justify-center space-x-2">
                                                    {{ ($this->tellerSheetAction)(['teller_id' => $data['teller']->id]) }}
                                                    {{ ($this->tallySheetAction)(['teller_id' => $data['teller']->id, 'date' => $selectedDate]) }}
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
                    <p class="mt-1 text-sm text-gray-500">Select a coordinator, date, and click 'Generate Report' to view the coordinator report.</p>
                </div>
            @endif
            
          
            <!-- Teller Details View -->
            @if($viewingTellerDetails && $selectedTeller)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="p-4 sm:p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">{{ $selectedTeller['teller']->name }} - Teller Report</h2>
                                <p class="text-sm text-gray-500">Detailed sales report for {{ Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</p>
                            </div>
                            <button 
                                wire:click="backToCoordinatorView" 
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back
                            </button>
                        </div>
                        
                        <!-- Teller Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-sm font-medium text-gray-500">Total Sales</div>
                                <div class="text-xl font-semibold text-blue-600">{{ number_format($selectedTeller['total_sales'], 2) }}</div>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-sm font-medium text-gray-500">Total Hits</div>
                                <div class="text-xl font-semibold text-red-600">{{ number_format($selectedTeller['total_hits'], 2) }}</div>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-sm font-medium text-gray-500">Total Gross</div>
                                <div class="text-xl font-semibold text-green-600">{{ number_format($selectedTeller['total_gross'], 2) }}</div>
                            </div>
                        </div>
                        
                        <!-- Teller Draw Time Table -->
                        <div class="mb-4">
                            <h3 class="text-md font-semibold text-gray-900 mb-2">Draw Time Breakdown</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Draw Time</th>
                                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hits</th>
                                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gross</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($selectedTeller['draw_data'] as $data)
                                            <tr>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $data['formatted_time'] }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-blue-600">{{ number_format($data['sales'], 2) }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-red-600">{{ number_format($data['hits'], 2) }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-green-600">{{ number_format($data['gross'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <th scope="row" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TOTAL</th>
                                            <td class="px-4 py-2 text-right text-xs font-bold text-blue-600">{{ number_format($selectedTeller['total_sales'], 2) }}</td>
                                            <td class="px-4 py-2 text-right text-xs font-bold text-red-600">{{ number_format($selectedTeller['total_hits'], 2) }}</td>
                                            <td class="px-4 py-2 text-right text-xs font-bold text-green-600">{{ number_format($selectedTeller['total_gross'], 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <div class="flex justify-end mt-6">
                            <button 
                                onclick="printDiv('teller-details-print')" 
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Teller Report
                            </button>
                        </div>
                        
                        <!-- Hidden printable version -->
                        <div id="teller-details-print" class="hidden">
                            <div class="text-center mb-6">
                                <h1 class="text-2xl font-bold">LUCKY BET</h1>
                                <h2 class="text-xl font-bold">TELLER SALES REPORT</h2>
                                <p>{{ Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</p>
                                <p class="font-semibold mt-2">{{ $selectedTeller['teller']->name }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <table class="w-full border border-gray-300">
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Total Sales</th>
                                        <td class="border border-gray-300 px-4 py-2">{{ number_format($selectedTeller['total_sales'], 2) }}</td>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Total Hits</th>
                                        <td class="border border-gray-300 px-4 py-2">{{ number_format($selectedTeller['total_hits'], 2) }}</td>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Total Gross</th>
                                        <td class="border border-gray-300 px-4 py-2">{{ number_format($selectedTeller['total_gross'], 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <table class="w-full border border-gray-300">
                                <thead>
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Draw Time</th>
                                        <th class="border border-gray-300 px-4 py-2 text-right">Sales</th>
                                        <th class="border border-gray-300 px-4 py-2 text-right">Hits</th>
                                        <th class="border border-gray-300 px-4 py-2 text-right">Gross</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedTeller['draw_data'] as $data)
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2">{{ $data['formatted_time'] }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right">{{ number_format($data['sales'], 2) }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right">{{ number_format($data['hits'], 2) }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right">{{ number_format($data['gross'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-2 text-left">TOTAL</th>
                                        <td class="border border-gray-300 px-4 py-2 text-right font-bold">{{ number_format($selectedTeller['total_sales'], 2) }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-right font-bold">{{ number_format($selectedTeller['total_hits'], 2) }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-right font-bold">{{ number_format($selectedTeller['total_gross'], 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Printable Report Modal -->
            @if($printableReport)
                <div id="printable-report" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-900">Lucky Bet Coordinator Report</h2>
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
                                <h2 class="text-xl font-bold uppercase">COORDINATOR SALES REPORT</h2>
                                <p class="text-gray-600">{{ $printableReport['date'] }}</p>
                                <p class="text-gray-800 font-semibold mt-1">{{ $printableReport['coordinator'] }}</p>
                            </div>
                            
                            <!-- Summary Stats -->
                            <div class="mb-6">
                                <table class="min-w-full border border-gray-300">
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Total Sales</th>
                                        <td class="border border-gray-300 px-4 py-2 font-semibold">{{ number_format($printableReport['totals']['sales'], 2) }}</td>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Total Hits</th>
                                        <td class="border border-gray-300 px-4 py-2 font-semibold">{{ number_format($printableReport['totals']['hits'], 2) }}</td>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Total Gross</th>
                                        <td class="border border-gray-300 px-4 py-2 font-semibold">{{ number_format($printableReport['totals']['gross'], 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Teller Table -->
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-2">Teller Summary</h3>
                                <table class="min-w-full border border-gray-300">
                                    <thead>
                                        <tr>
                                            <th class="border border-gray-300 px-4 py-2 text-left">Teller's Name</th>
                                            <th class="border border-gray-300 px-4 py-2 text-right">Total Sales</th>
                                            <th class="border border-gray-300 px-4 py-2 text-right">Total Hits</th>
                                            <th class="border border-gray-300 px-4 py-2 text-right">Total Gross</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($printableReport['tellers'] as $data)
                                            <tr>
                                                <td class="border border-gray-300 px-4 py-2">{{ $data['teller']->name }}</td>
                                                <td class="border border-gray-300 px-4 py-2 text-right">{{ number_format($data['total_sales'], 2) }}</td>
                                                <td class="border border-gray-300 px-4 py-2 text-right">{{ number_format($data['total_hits'], 2) }}</td>
                                                <td class="border border-gray-300 px-4 py-2 text-right">{{ number_format($data['total_gross'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th class="border border-gray-300 px-4 py-2 text-left">TOTAL</th>
                                            <td class="border border-gray-300 px-4 py-2 text-right font-bold">{{ number_format($printableReport['totals']['sales'], 2) }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right font-bold">{{ number_format($printableReport['totals']['hits'], 2) }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right font-bold">{{ number_format($printableReport['totals']['gross'], 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            <script>
                function printDiv(divName) {
                    var printContents = document.getElementById(divName).innerHTML;
                    var originalContents = document.body.innerHTML;
                    document.body.innerHTML = printContents;
                    window.print();
                    document.body.innerHTML = originalContents;
                }
            </script>
          <x-filament-actions::modals />
        </div>
    </x-admin>
</div>
