<div>
    <x-admin>
        <div class="flex justify-between items-center mb-4">
            <span class="text-sm font-semibold text-gray-700">Draw Date:
                <span class="ml-2 bg-primary-100 text-primary-800 text-xs font-bold px-2.5 py-0.5 rounded">
                    {{ \Carbon\Carbon::parse($this->filterDate)->format('F j, Y') }}
                </span>
            </span>
        </div>

        <!-- Stats Section -->
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="bg-white p-4 rounded-xl shadow border">
                <h3 class="text-sm font-bold text-gray-800 mb-2">Summary</h3>
                <p class="text-xs text-gray-600">Total Ratios</p>
                <p class="text-lg font-bold text-blue-600">{{ $betRatioStats['total_ratios'] }}</p>

                <p class="text-xs text-gray-600 mt-2">Total Max Amount</p>
                <p class="text-lg font-bold text-green-600">&#8369;{{ number_format($betRatioStats['total_max_amount'], 2) }}</p>

                <p class="text-xs text-gray-600 mt-2">Average Cap</p>
                <p class="text-lg font-bold text-indigo-600">&#8369;{{ number_format($betRatioStats['avg_max_amount'], 2) }}</p>
            </div>

            <div class="bg-white p-4 rounded-xl shadow border">
                <h3 class="text-sm font-bold text-gray-800 mb-2">Game Type Stats</h3>
                <ul class="space-y-1 text-sm">
                    @foreach($betRatioStats['game_type_stats'] as $name => $stat)
                        <li class="flex justify-between">
                            <span>{{ $name }}</span>
                            <span class="text-gray-700 font-semibold">{{ $stat['total'] }} caps / ₱{{ number_format($stat['total_max_amount'], 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            
            <div class="bg-white p-4 rounded-xl shadow border">
                <h3 class="text-sm font-bold text-gray-800 mb-2">Location Stats</h3>
                <ul class="space-y-1 text-sm">
                    @foreach($betRatioStats['location_stats'] as $name => $stat)
                        <li class="flex justify-between">
                            <span>{{ $name }}</span>
                            <span class="text-gray-700 font-semibold">{{ $stat['total'] }} caps / ₱{{ number_format($stat['total_max_amount'], 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Table Component -->
        <div class="bg-white rounded-xl shadow">
            {{ $this->table }}
        </div>
    </x-admin>
</div>
