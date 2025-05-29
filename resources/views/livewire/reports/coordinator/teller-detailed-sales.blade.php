<div class="p-4">
    @if($teller)
        <div class="mb-4">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-xl font-semibold text-gray-800">{{ $teller['name'] }}</h2>
                    <p class="text-sm text-gray-600">Sales for {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-3 rounded-md shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Sales</h3>
                    <p class="text-xl font-bold text-blue-600">{{ number_format($teller['total_sales'], 2) }}</p>
                </div>
                <div class="bg-red-50 p-3 rounded-md shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Hits</h3>
                    <p class="text-xl font-bold text-red-600">{{ number_format($teller['total_hits'], 2) }}</p>
                </div>
                <div class="bg-green-50 p-3 rounded-md shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Gross</h3>
                    <p class="text-xl font-bold text-green-600">{{ number_format($teller['total_gross'], 2) }}</p>
                </div>
            </div>
        </div>
        
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-800 mb-3">Sales by Game Type</h3>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hits</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if(isset($teller['game_types']) && count($teller['game_types']) > 0)
                            @foreach($teller['game_types'] as $gameType)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $gameType['name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">{{ number_format($gameType['total_sales'], 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">{{ number_format($gameType['total_hits'], 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">{{ number_format($gameType['total_gross'], 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No game type data available</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="text-xs text-gray-500 mt-4">
            <p>Note: Gross amount is calculated as Sales minus Hits.</p>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded relative" role="alert">
            <p>No data available for this teller.</p>
        </div>
    @endif
</div>
