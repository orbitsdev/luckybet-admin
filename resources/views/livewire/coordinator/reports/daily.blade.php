<div>
    <x-admin>
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Daily Sales Report</h2>
    
    <!-- Date Filter -->
    <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 mb-6">
        <div class="md:grid md:grid-cols-4 md:gap-6">
            <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Date Filter</h3>
                <p class="mt-1 text-sm text-gray-500">Select a date to view the report.</p>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-3">
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                    <div>
                        <label for="report_date" class="block text-sm font-medium text-gray-700">Date</label>
                        <div class="mt-1">
                            <input type="date" id="report_date" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Generate Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Report Content -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Daily Sales Summary</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">For: <span class="font-medium">June 3, 2025</span></p>
            </div>
            <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export PDF
            </button>
        </div>
        
        <!-- Summary Cards -->
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Bets Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Total Bets
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            1,234
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Total Amount Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Total Amount
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            ₱45,678.00
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Winning Amount Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Winning Amount
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            ₱12,345.00
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Net Profit Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Net Profit
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            ₱33,333.00
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Game Type Breakdown -->
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Game Type Breakdown</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Game Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Bets
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Amount
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Winning Amount
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Profit/Loss
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                S3
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                450
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱18,000.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱4,500.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                ₱13,500.00
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                S2
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                320
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱12,800.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱3,200.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                ₱9,600.00
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                D4
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                280
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱8,400.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱2,800.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                ₱5,600.00
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                D4-S2
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                120
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱3,600.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱1,200.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                ₱2,400.00
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                D4-S3
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                64
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱2,878.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱645.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                ₱2,233.00
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                                Total
                            </th>
                            <td class="px-6 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                                1,234
                            </td>
                            <td class="px-6 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                                ₱45,678.00
                            </td>
                            <td class="px-6 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                                ₱12,345.00
                            </td>
                            <td class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-green-600">
                                ₱33,333.00
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <!-- Teller Performance -->
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Teller Performance</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Teller
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Bets
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Amount
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Winning Amount
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Profit/Loss
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                John Doe
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                450
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱18,000.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱4,500.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                ₱13,500.00
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Jane Smith
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                320
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱12,800.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱3,200.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                ₱9,600.00
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Mike Johnson
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                280
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱8,400.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱2,800.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                ₱5,600.00
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Sarah Williams
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                184
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱6,478.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ₱1,845.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                ₱4,633.00
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                                Total
                            </th>
                            <td class="px-6 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                                1,234
                            </td>
                            <td class="px-6 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                                ₱45,678.00
                            </td>
                            <td class="px-6 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                                ₱12,345.00
                            </td>
                            <td class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-green-600">
                                ₱33,333.00
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    </x-admin>
</div>
