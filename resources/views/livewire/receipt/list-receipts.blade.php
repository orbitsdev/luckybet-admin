<div>
    <x-admin>
        <!-- Stats Section -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left column: Summary stats -->
            <div class="bg-white rounded-xl overflow-hidden shadow">
                <div class="p-4 bg-blue-50 border-b border-blue-100">
                    <h2 class="text-lg font-semibold text-blue-800">Receipt Summary</h2>
                    <p class="text-sm text-blue-600">{{ \Carbon\Carbon::parse($filterDate)->format('F j, Y') }}</p>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <p class="text-sm text-blue-600">Total Receipts</p>
                            <p class="text-2xl font-bold text-blue-800">{{ number_format($receiptStats['total_receipts'] ?? 0) }}</p>
                        </div>
                        <div class="p-3 bg-green-50 rounded-lg">
                            <p class="text-sm text-green-600">Total Amount</p>
                            <p class="text-2xl font-bold text-green-800">₱{{ number_format($receiptStats['total_amount'] ?? 0, 2) }}</p>
                        </div>
                    </div>

                    <!-- Status breakdown -->
                    <div class="mt-4">
                        <h3 class="text-sm font-medium text-gray-600 mb-2">Status Breakdown</h3>
                        <div class="space-y-2">
                            @foreach($receiptStats['by_status'] ?? [] as $status => $data)
                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                    <span class="inline-flex items-center">
                                        @if($status == 'placed')
                                            <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                                            <span class="text-sm">Placed</span>
                                        @elseif($status == 'cancelled')
                                            <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                                            <span class="text-sm">Cancelled</span>
                                        @else
                                            <span class="w-3 h-3 bg-gray-500 rounded-full mr-2"></span>
                                            <span class="text-sm">{{ ucfirst($status) }}</span>
                                        @endif
                                    </span>
                                    <div class="flex space-x-4">
                                        <span class="text-sm font-medium">{{ $data['count'] ?? 0 }}</span>
                                        <span class="text-sm text-gray-500">₱{{ number_format($data['total_amount'] ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle column: Top Tellers -->
            <div class="bg-white rounded-xl overflow-hidden shadow">
                <div class="p-4 bg-purple-50 border-b border-purple-100">
                    <h2 class="text-lg font-semibold text-purple-800">Top Tellers</h2>
                    <p class="text-sm text-purple-600">By receipt count</p>
                </div>
                <div class="p-4">
                    @if(count($receiptStats['top_tellers'] ?? []) > 0)
                        <div class="space-y-3">
                            @foreach($receiptStats['top_tellers'] ?? [] as $teller)
                                <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                    <span class="text-sm font-medium">{{ $teller->name }}</span>
                                    <div class="flex space-x-4">
                                        <span class="text-sm bg-purple-100 text-purple-800 py-0.5 px-2 rounded-full">{{ $teller->receipt_count }}</span>
                                        <span class="text-sm text-gray-500">₱{{ number_format($teller->total_amount, 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-4 text-center text-gray-500">
                            <p>No data available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right column: Top Locations -->
            <div class="bg-white rounded-xl overflow-hidden shadow">
                <div class="p-4 bg-amber-50 border-b border-amber-100">
                    <h2 class="text-lg font-semibold text-amber-800">Top Locations</h2>
                    <p class="text-sm text-amber-600">By receipt count</p>
                </div>
                <div class="p-4">
                    @if(count($receiptStats['top_locations'] ?? []) > 0)
                        <div class="space-y-3">
                            @foreach($receiptStats['top_locations'] ?? [] as $location)
                                <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                    <span class="text-sm font-medium">{{ $location->name }}</span>
                                    <div class="flex space-x-4">
                                        <span class="text-sm bg-amber-100 text-amber-800 py-0.5 px-2 rounded-full">{{ $location->receipt_count }}</span>
                                        <span class="text-sm text-gray-500">₱{{ number_format($location->total_amount, 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-4 text-center text-gray-500">
                            <p>No data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Receipts Table -->
        <div class="mb-4 bg-white rounded-xl overflow-hidden shadow">
            {{ $this->table }}
        </div>
    </x-admin>
</div>
