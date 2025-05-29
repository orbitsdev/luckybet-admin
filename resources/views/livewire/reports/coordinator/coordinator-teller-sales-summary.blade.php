<div>
    <x-admin>
        <div class="p-4 bg-white rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('reports.summary') }}" class="inline-flex items-center px-2 py-1 text-sm text-gray-700 hover:text-indigo-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Summary
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800">Coordinator Tellers Sales Summary</h1>
                </div>
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" wire:model.live="date" id="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>
            
            @if($coordinatorData)
                <div class="mb-4 bg-indigo-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-xl font-semibold text-gray-800">Coordinator: {{ $coordinatorData['name'] }}</h2>
                            <p class="text-sm text-gray-600">Viewing all tellers for this coordinator</p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-white p-3 rounded-md shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Total Sales</h3>
                                <p class="text-2xl font-bold text-blue-600">{{ number_format($totalSales, 2) }}</p>
                            </div>
                            <div class="bg-white p-3 rounded-md shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Total Hits</h3>
                                <p class="text-2xl font-bold text-red-600">{{ number_format($totalHits, 2) }}</p>
                            </div>
                            <div class="bg-white p-3 rounded-md shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Total Gross</h3>
                                <p class="text-2xl font-bold text-green-600">{{ number_format($totalGross, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-2 text-sm text-gray-600">
                    <span class="font-medium">Date:</span> {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}
                </div>
                
                @if(count($salesData) > 0)
                    <div class="mb-8 bg-white rounded-lg shadow overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teller Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hits</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($salesData as $teller)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $teller['name'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">{{ number_format($teller['total_sales'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">{{ number_format($teller['total_hits'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">{{ number_format($teller['total_gross'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ ($this->viewTellerDetailsAction)(['teller_id' => $teller['id']]) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded relative" role="alert">
                        <p>No sales data found for the selected date.</p>
                    </div>
                @endif
            @else
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded relative" role="alert">
                    <p>No teller found or teller does not belong to you.</p>
                </div>
            @endif
        </div>
        
        <x-filament-actions::modals />
    </x-admin>
</div>
