<div>
    <x-admin>
        <!-- Back button -->
        <div class="mb-4">
            <a href="{{ route('manage.receipts') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Receipts
            </a>
        </div>

        <!-- Receipt Header -->
        <div class="mb-6 bg-white rounded-xl overflow-hidden shadow">
            <div class="p-4 bg-blue-50 border-b border-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-semibold text-blue-800">Receipt #{{ $receipt->ticket_id }}</h2>
                        <p class="text-sm text-blue-600">{{ \Carbon\Carbon::parse($receipt->receipt_date)->format('F j, Y') }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($receipt->status === 'placed') bg-green-100 text-green-800
                            @elseif($receipt->status === 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($receipt->status) }}
                        </span>
                        <!-- QR Code for Ticket ID -->
                        <div class="flex flex-col items-center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ $receipt->ticket_id }}" 
                                alt="Receipt QR Code" 
                                class="h-16 w-16 border border-gray-200 rounded shadow-sm">
                            <span class="text-xs text-gray-500 mt-1">Scan for verification</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Receipt Info -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Receipt Information</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Teller:</span>
                                <span class="text-sm font-medium">{{ $receipt->teller->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Location:</span>
                                <span class="text-sm font-medium">{{ $receipt->location->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Date:</span>
                                <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($receipt->receipt_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Time:</span>
                                <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($receipt->created_at)->format('h:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bet Summary -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Bet Summary</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Bets:</span>
                                <span class="text-sm font-medium">{{ number_format($receiptStats['total_bets']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Amount:</span>
                                <span class="text-sm font-medium text-green-600">₱{{ number_format($receipt->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Game Type Distribution -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Game Type Distribution</h3>
                        <div class="space-y-2">
                            @foreach($receiptStats['by_game_type'] as $gameType => $data)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">{{ $data['name'] }}:</span>
                                    <div class="flex space-x-4">
                                        <span class="text-sm bg-blue-100 text-blue-800 py-0.5 px-2 rounded-full">{{ $data['count'] }}</span>
                                        <span class="text-sm font-medium">₱{{ number_format($data['amount'], 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Draw Time Distribution -->
        @if(count($receiptStats['by_draw_time'] ?? []) > 0)
            <div class="mb-6 bg-white rounded-xl overflow-hidden shadow">
                <div class="p-4 bg-purple-50 border-b border-purple-100">
                    <h2 class="text-lg font-semibold text-purple-800">Draw Time Distribution</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($receiptStats['by_draw_time'] as $drawTime => $data)
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($drawTime)->format('h:i A') }}</span>
                                    <div class="flex space-x-4">
                                        <span class="text-sm bg-purple-100 text-purple-800 py-0.5 px-2 rounded-full">{{ $data['count'] }}</span>
                                        <span class="text-sm text-gray-500">₱{{ number_format($data['amount'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Bets Table -->
        <div class="mb-4 bg-white rounded-xl overflow-hidden shadow">
            <div class="p-4 bg-green-50 border-b border-green-100">
                <h2 class="text-lg font-semibold text-green-800">Bets</h2>
                <p class="text-sm text-green-600">All bets placed under this receipt</p>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bet Number</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket ID</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game Type</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">D4 Sub-Selection</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Draw Time</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payout</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($receipt->bets as $bet)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $bet->bet_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">{{ $bet->ticket_id ?? $receipt->ticket_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $bet->gameType->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $bet->d4_sub_selection ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $bet->draw ? \Carbon\Carbon::parse($bet->draw->draw_time)->format('h:i A') : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($bet->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($bet->status === 'won') bg-green-100 text-green-800
                                        @elseif($bet->status === 'lost') bg-red-100 text-red-800
                                        @elseif($bet->status === 'cancelled') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($bet->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($bet->payout ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-admin>
</div>
