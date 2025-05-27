<div>
    <x-admin>
        <!-- Main content in 2-column grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Left column: Statistics -->
            <div class="col-span-1 mb-4 bg-gray-50 rounded-xl shadow-sm">
                <!-- Summary Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4">
                    <!-- Total Tellers with Commission -->
                    <div class="rounded-xl shadow p-3 text-center bg-white text-gray-800">
                        <div class="flex justify-center mb-2">
                            <div class="bg-gray-100 rounded-full p-2">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-6a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 10-8 0 4 4 0 008 0z"/></svg>
                            </div>
                        </div>
                        <div class="text-xs font-semibold text-gray-500">Tellers with Commission</div>
                        <div class="text-xl font-bold">{{ number_format($commissionStats['total_tellers'] ?? 0) }}</div>
                    </div>
                    <!-- Average Commission Rate -->
                    <div class="rounded-xl shadow p-3 text-center bg-white text-gray-800">
                        <div class="flex justify-center mb-2">
                            <div class="bg-gray-100 rounded-full p-2">
                                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                        <div class="text-xs font-semibold text-gray-500">Average Commission Rate</div>
                        <div class="text-xl font-bold">{{ number_format($commissionStats['avg_rate'] ?? 0, 2) }}%</div>
                    </div>
                </div>

                <!-- Location Statistics -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden m-4">
                    <div class="bg-gray-50 px-3 py-2 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-800">Commission by Location</h3>
                    </div>
                    <div class="p-3">
                        @if(!empty($commissionStats['location_stats']))
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Location</th>
                                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Tellers</th>
                                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Avg Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($commissionStats['location_stats'] as $locationId => $location)
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-900">{{ $location['name'] }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-right text-gray-700">{{ number_format($location['teller_count']) }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-right text-gray-700">{{ number_format($location['avg_rate'] ?? 0, 2) }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 text-gray-500">No location data available.</div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Right column: Commission table -->
            <div class="mb-4 bg-white rounded-xl overflow-hidden col-span-1 md:col-span-2 shadow">
                {{ $this->table }}
            </div>
        </div>
    </x-admin>
</div>
