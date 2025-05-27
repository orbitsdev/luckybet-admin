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
            <div class="col-span-1 mb-4 bg-gray-50 rounded-xl shadow-sm p-3">
                
                    
                    <!-- Branch Statistics in Grid Layout with Collapsible Sections -->
                    <div class="space-y-3">
                        @foreach($drawStats['location_stats'] as $locationId => $locationData)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                                <!-- Collapsible Header -->
                                <div class="bg-gray-50 px-3 py-2 border-b border-gray-100 flex justify-between items-center cursor-pointer" 
                                     onclick="toggleBranchStats('branch-{{ $locationId }}')">
                                    <h3 class="text-sm font-semibold text-gray-800">
                                        {{ $locationData['name'] }} 
                                        <span class="text-sm text-primary-600 ml-1 font-bold">
                                            ({{ $locationData['total_hits'] }} hits)
                                        </span>
                                    </h3>
                                    <svg id="branch-{{ $locationId }}-icon-expand" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    <svg id="branch-{{ $locationId }}-icon-collapse" class="h-4 w-4 text-gray-500 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </div>
                                
                                <!-- Collapsible Content -->
                                <div id="branch-{{ $locationId }}-content" class="overflow-x-auto p-2">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-3 py-2 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">Teller</th>
                                                <th scope="col" class="px-3 py-2 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">Hits</th>
                                                @foreach($drawStats['game_types'] as $key => $label)
                                                    <th scope="col" class="px-3 py-2 text-center text-sm font-medium text-gray-700 uppercase tracking-wider">{{ $label }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($locationData['tellers'] as $tellerId => $tellerStats)
                                                <tr>
                                                    <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $tellerStats['name'] }}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">{{ $tellerStats['total_hits'] }}</td>
                                                    @foreach($drawStats['game_types'] as $key => $label)
                                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-center text-gray-700">
                                                            @if($tellerStats['game_types'][$key] > 0)
                                                                <span class="px-2 py-1 inline-flex text-sm leading-4 font-semibold rounded-full bg-green-100 text-green-800">
                                                                    {{ $tellerStats['game_types'][$key] }}
                                                                </span>
                                                            @else
                                                                <span class="text-gray-400">-</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50">
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">Total</td>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-primary-600">{{ $locationData['total_hits'] }}</td>
                                                @foreach($drawStats['game_types'] as $key => $label)
                                                    <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-primary-600 text-center">
                                                        {{ $locationData['game_types'][$key] }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if(!empty($drawStats['location_stats']))
                    <!-- Grand Total Summary at the top -->
                    <div class="mb-4 p-3 bg-white rounded-lg shadow-sm border border-gray-100">
                        <h3 class="text-sm font-bold text-gray-800 mb-2 border-b pb-2 px-1">Grand Total Summary</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-3 py-2 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">All Branches</th>
                                        <th scope="col" class="px-3 py-2 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">Total Hits</th>
                                        @foreach($drawStats['game_types'] as $key => $label)
                                            <th scope="col" class="px-3 py-2 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">{{ $label }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-white">
                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-bold text-gray-900">Total</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-bold text-primary-600">{{ $drawStats['total_hits'] }}</td>
                                        @foreach($drawStats['game_types'] as $key => $label)
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-bold text-primary-600">
                                                @php
                                                    $total = 0;
                                                    foreach($drawStats['location_stats'] as $locationId => $locationData) {
                                                        $total += $locationData['game_types'][$key];
                                                    }
                                                @endphp
                                                {{ $total }}
                                            </td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4 text-gray-500">No data available for the selected date.</div>
                @endif
            </div>
            
            <!-- Right column: Draw table -->
            <div class="mb-4 bg-white rounded-xl overflow-hidden col-span-2">
                {{ $this->table }}
            </div>
        </div>
    </x-admin>
</div>

<script>
    // JavaScript for toggling branch statistics visibility
    function toggleBranchStats(branchId) {
        const content = document.getElementById(branchId + '-content');
        const expandIcon = document.getElementById(branchId + '-icon-expand');
        const collapseIcon = document.getElementById(branchId + '-icon-collapse');
        
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
    
    // Initialize all branch sections as expanded
    document.addEventListener('DOMContentLoaded', function() {
        const branchContents = document.querySelectorAll('[id$="-content"]');
        const expandIcons = document.querySelectorAll('[id$="-icon-expand"]');
        const collapseIcons = document.querySelectorAll('[id$="-icon-collapse"]');
        
        expandIcons.forEach(icon => {
            icon.classList.add('hidden');
        });
        
        collapseIcons.forEach(icon => {
            icon.classList.remove('hidden');
        });
    });
</script>
