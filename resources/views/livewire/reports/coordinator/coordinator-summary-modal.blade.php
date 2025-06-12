<div class="p-4">
    <div class="mb-4 bg-indigo-50 p-4 rounded-lg">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <h2 class="text-xl font-semibold text-gray-800">{{ $coordinator['name'] }}</h2>
                <p class="text-sm text-gray-600">Sales summary for {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</p>
            </div>
        </div>
    </div>
    
    <div class="mb-6">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="bg-white p-3 rounded-md shadow-sm border border-gray-100">
                <h3 class="text-sm font-medium text-gray-500">Total Sales</h3>
                <p class="text-2xl font-bold {{ $coordinator['total_sales'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($coordinator['total_sales'], 2) }}</p>
            </div>
            <div class="bg-white p-3 rounded-md shadow-sm border border-gray-100">
                <h3 class="text-sm font-medium text-gray-500">Total Hits</h3>
                <p class="text-2xl font-bold {{ $coordinator['total_hits'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($coordinator['total_hits'], 2) }}</p>
            </div>
            <div class="bg-white p-3 rounded-md shadow-sm border border-gray-100">
                <h3 class="text-sm font-medium text-gray-500">Total Gross</h3>
                <p class="text-2xl font-bold {{ $coordinator['total_gross'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($coordinator['total_gross'], 2) }}</p>
            </div>
        </div>
    </div>
    
    <div class="mb-4">
        <h3 class="text-lg font-medium text-gray-800 mb-3">Teller Sales Summary</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teller Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hits</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($tellerData as $teller)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $teller['name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $teller['sales'] >= 0 ? 'text-green-600' : 'text-red-600' }} font-bold">{{ number_format($teller['sales'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $teller['hits'] >= 0 ? 'text-green-600' : 'text-red-600' }} font-bold">{{ number_format($teller['hits'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $teller['gross'] >= 0 ? 'text-green-600' : 'text-red-600' }} font-bold">{{ number_format($teller['gross'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mb-4 grid grid-cols-2 gap-4">
        <div class="bg-gray-50 p-3 rounded-md">
            <h4 class="text-sm font-medium text-gray-500">Tellers</h4>
            <div class="flex items-center mt-1">
                <span class="text-xl font-bold text-gray-800">{{ $activeTellerCount }}</span>
                <span class="text-sm text-gray-500 ml-2">active / {{ $tellerCount }} total</span>
            </div>
        </div>
        <div class="bg-gray-50 p-3 rounded-md">
            <h4 class="text-sm font-medium text-gray-500">Commission Rate</h4>
            <div class="flex items-center mt-1">
                <span class="text-xl font-bold text-gray-800">{{ number_format($commissionRate, 3) }}%</span>
            </div>
        </div>
    </div>
    

    
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-800 mb-3">Additional Information</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-3 rounded-md">
                <h4 class="text-sm font-medium text-gray-500">Bets</h4>
                <div class="flex items-center mt-1">
                    <span class="text-xl font-bold text-gray-800">{{ $betCount }}</span>
                    <span class="text-sm text-gray-500 ml-2">total ({{ $winningBetCount }} winners)</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-6 flex justify-between items-center">
        <div class="text-sm text-gray-500">
            <span class="font-medium">Commission Rate:</span> {{ number_format($coordinator['total_sales'] > 0 ? ($coordinator['total_gross'] / $coordinator['total_sales']) * 100 : 0, 1) }}%
        </div>
        <a href="{{ route('reports.teller-sales-summary', ['coordinator_id' => $coordinator['id']]) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:border-red-900 focus:ring focus:ring-red-300 disabled:opacity-25 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            VIEW DETAILED REPORT
        </a>
    </div>
</div>
