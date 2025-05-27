<div>
    <x-admin>
        <div class="mb-4 p-3 bg-white rounded shadow">
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm font-medium text-gray-700">Draw Date: 
                    <span class="ml-2 inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                        {{ \Carbon\Carbon::parse($this->filterDate)->format('F j, Y') }}
                    </span>
                </span>
            </div>
            
            @if(!empty($drawStats['teller_game_stats']))
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teller</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hits</th>
                                @foreach($drawStats['game_types'] as $key => $label)
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($drawStats['teller_game_stats'] as $teller => $stats)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $teller }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $stats['total_hits'] }}</td>
                                    @foreach($drawStats['game_types'] as $key => $label)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($stats['game_types'][$key] > 0)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ $stats['game_types'][$key] }}
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Total</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $drawStats['total_hits'] }}</td>
                                @foreach($drawStats['game_types'] as $key => $label)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        @php
                                            $total = 0;
                                            foreach($drawStats['teller_game_stats'] as $teller => $stats) {
                                                $total += $stats['game_types'][$key];
                                            }
                                        @endphp
                                        {{ $total }}
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-gray-500">No data available for the selected date.</div>
            @endif
        </div>
        
        {{ $this->table }}
    </x-admin>
</div>

@script
<script>
    // Force refresh when filter changes
    $wire.on('refresh', () => {
        window.setTimeout(() => {
            $wire.$refresh();
        }, 100);
    });
</script>
@endscript

