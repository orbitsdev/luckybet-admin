<div>
    <x-admin>
        <div class="flex justify-between items-center mb-3">
            <span class="text-sm font-medium text-gray-700">Draw Date: 
                <span class="ml-2 inline-block bg-primary-100 text-primary-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                    {{ \Carbon\Carbon::parse($this->filterDate)->format('F j, Y') }}
                </span>
            </span>
        </div>

        <!-- Main content in 3-column grid -->
        <div class="grid grid-cols-3 gap-4">
            <!-- Left column: Statistics -->
            <div class="col-span-1 mb-4 bg-gray-50 rounded-xl shadow-sm">
                @if(!empty($betRatioStats['location_stats']))
                    <!-- Summary Stats -->
                    <div class="p-3 bg-white rounded-lg shadow-sm border border-gray-100 mb-4">
                        <h3 class="text-sm font-bold text-gray-800 mb-2 border-b pb-2 px-1">Summary Statistics</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-blue-50 p-2 rounded-lg">
                                <p class="text-xs text-gray-500">Total Bet Ratios</p>
                                <p class="text-lg font-bold text-blue-600">{{ $betRatioStats['total_bet_ratios'] }}</p>
                            </div>
                            <div class="bg-green-50 p-2 rounded-lg">
                                <p class="text-xs text-gray-500">Total Max Amount</p>
                                <p class="text-lg font-bold text-green-600">₱{{ number_format($betRatioStats['total_max_amount'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Draw Time Statistics -->
                    <div class="p-3 bg-white rounded-lg shadow-sm border border-gray-100 mb-4">
                        <h3 class="text-sm font-bold text-gray-800 mb-2 border-b pb-2 px-1">Draw Time Statistics</h3>
                        <div class="space-y-2">
                            @foreach($betRatioStats['draw_time_stats'] as $drawTime => $stats)
                                <div class="bg-gray-50 p-2 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <p class="text-sm font-medium text-gray-700">{{ \Carbon\Carbon::parse($drawTime)->format('g:i A') }}</p>
                                        <p class="text-xs font-semibold text-blue-600">{{ $stats['total'] }} ratios</p>
                                    </div>
                                    <div class="mt-1">
                                        <p class="text-xs text-gray-500">Total Max Amount: <span class="font-semibold text-green-600">₱{{ number_format($stats['total_max_amount'], 2) }}</span></p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Game Type Statistics -->
                    <div class="p-3 bg-white rounded-lg shadow-sm border border-gray-100 mb-4">
                        <h3 class="text-sm font-bold text-gray-800 mb-2 border-b pb-2 px-1">Game Type Statistics</h3>
                        <div class="space-y-2">
                            @foreach($betRatioStats['game_type_stats'] as $gameTypeId => $stats)
                                <div class="bg-gray-50 p-2 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <p class="text-sm font-medium text-gray-700">{{ $stats['name'] }}</p>
                                        <p class="text-xs font-semibold text-blue-600">{{ $stats['total'] }} ratios</p>
                                    </div>
                                    <div class="mt-1">
                                        <p class="text-xs text-gray-500">Total Max Amount: <span class="font-semibold text-green-600">₱{{ number_format($stats['total_max_amount'], 2) }}</span></p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Location Statistics in Collapsible Sections -->
                    <div class="space-y-3">
                        @foreach($betRatioStats['location_stats'] as $locationId => $locationData)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                                <!-- Collapsible Header -->
                                <div class="bg-gray-50 px-3 py-2 border-b border-gray-100 flex justify-between items-center cursor-pointer" 
                                     onclick="toggleLocationStats('location-{{ $locationId }}')">
                                    <h3 class="text-sm font-semibold text-gray-800">
                                        {{ $locationData['name'] }} 
                                        <span class="text-sm text-primary-600 ml-1 font-bold">
                                            ({{ $locationData['total'] }} ratios)
                                        </span>
                                    </h3>
                                    <svg id="location-{{ $locationId }}-icon-expand" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    <svg id="location-{{ $locationId }}-icon-collapse" class="h-4 w-4 text-gray-500 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </div>
                                
                                <!-- Collapsible Content -->
                                <div id="location-{{ $locationId }}-content" class="overflow-x-auto p-2">
                                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-3 py-0.5 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Game Type</th>
                                                <th scope="col" class="px-3 py-0.5 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Count</th>
                                                <th scope="col" class="px-3 py-0.5 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Max Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($locationData['game_types'] as $gameTypeId => $gameTypeStats)
                                                <tr>
                                                    <td class="px-3 py-0.5 whitespace-nowrap text-xs font-medium text-gray-900">{{ $gameTypeStats['name'] }}</td>
                                                    <td class="px-3 py-0.5 whitespace-nowrap text-xs text-gray-700">{{ $gameTypeStats['total'] }}</td>
                                                    <td class="px-3 py-0.5 whitespace-nowrap text-xs text-gray-700">₱{{ number_format($gameTypeStats['total_max_amount'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50">
                                            <tr>
                                                <td class="px-3 py-0.5 whitespace-nowrap text-xs font-medium text-gray-900">Total</td>
                                                <td class="px-3 py-0.5 whitespace-nowrap text-xs font-medium text-primary-600">{{ $locationData['total'] }}</td>
                                                <td class="px-3 py-0.5 whitespace-nowrap text-xs font-medium text-primary-600">₱{{ number_format($locationData['total_max_amount'], 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 px-4">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No bet ratios found</h3>
                        <p class="mt-1 text-sm text-gray-500">No bet ratio data available for {{ \Carbon\Carbon::parse($this->filterDate)->format('F j, Y') }}.</p>
                        <div class="mt-3">
                            <button type="button" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" onclick="Livewire.dispatch('filament.table.filters.reset')">
                                <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Reset to Today
                            </button>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Right column: Bet Ratios table -->
            <div class="mb-4 bg-white rounded-xl overflow-hidden col-span-2 shadow">
                {{ $this->table }}
            </div>
        </div>

        @push('scripts')
        <script>
            // JavaScript for toggling location statistics visibility
            function toggleLocationStats(locationId) {
                const content = document.getElementById(locationId + '-content');
                const expandIcon = document.getElementById(locationId + '-icon-expand');
                const collapseIcon = document.getElementById(locationId + '-icon-collapse');
                
                if (content.style.display === 'none') {
                    content.style.display = 'block';
                    expandIcon.classList.add('hidden');
                    collapseIcon.classList.remove('hidden');
                } else {
                    content.style.display = 'none';
                    expandIcon.classList.remove('hidden');
                    collapseIcon.classList.add('hidden');
                }
            }
            
            // Initialize all location sections as expanded
            document.addEventListener('DOMContentLoaded', function() {
                const locationContents = document.querySelectorAll('[id$="-content"]');
                const expandIcons = document.querySelectorAll('[id$="-icon-expand"]');
                const collapseIcons = document.querySelectorAll('[id$="-icon-collapse"]');
                
                expandIcons.forEach(icon => {
                    icon.classList.add('hidden');
                });
                
                collapseIcons.forEach(icon => {
                    icon.classList.remove('hidden');
                });
            });
            
            // Listen for stats-updated event to ensure statistics are refreshed
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('stats-updated', () => {
                    // Force a refresh of the statistics section
                    Livewire.dispatch('compute-stats');
                });
            });
        </script>
        @endpush
    </x-admin>
</div>
