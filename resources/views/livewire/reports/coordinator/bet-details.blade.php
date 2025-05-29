<div class="p-4">
    @if($bet)
        @if($bet->ticket_id)
            <div class="mb-4 flex justify-center">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($bet->ticket_id) }}" 
                         alt="Ticket QR Code" class="mx-auto mb-2">
                    <p class="text-sm text-gray-500">Ticket ID: <span class="font-medium">{{ $bet->ticket_id }}</span></p>
                </div>
            </div>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Bet Information -->
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Bet Information</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Ticket ID:</span>
                        <span class="font-medium">{{ $bet->ticket_id ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Bet Number:</span>
                        <span class="font-medium">{{ $bet->bet_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Game Type:</span>
                        <span class="font-medium">
                            {{ $bet->gameType->name }}
                            @if($bet->d4_sub_selection)
                                <span class="text-xs text-gray-500">({{ $bet->d4_sub_selection }})</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Amount:</span>
                        <span class="font-medium text-blue-600">{{ number_format($bet->amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Winning Amount:</span>
                        <span class="font-medium {{ $bet->winning_amount > 0 ? 'text-red-600' : 'text-gray-500' }}">
                            {{ $bet->winning_amount > 0 ? number_format($bet->winning_amount, 2) : '-' }}
                        </span>
                    </div>
                    @php $grossAmount = $bet->amount - $bet->winning_amount; @endphp
                    <div class="flex justify-between">
                        <span class="text-gray-500">Gross:</span>
                        <span class="font-medium {{ $grossAmount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($grossAmount, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Commission Rate:</span>
                        <span class="font-medium text-purple-600">
                            {{ $bet->commission_rate ? number_format($bet->commission_rate * 100, 0) . '%' : '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Commission Amount:</span>
                        <span class="font-medium text-purple-600">
                            {{ $bet->commission_amount ? number_format($bet->commission_amount, 2) : '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status:</span>
                        <span>
                            @if($bet->is_rejected)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Rejected
                                </span>
                            @elseif($bet->winning_amount > 0)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Winner
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Placed
                                </span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Is Combination:</span>
                        <span>
                            @if($bet->is_combination)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Yes
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    No
                                </span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Draw and User Information -->
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Draw & User Information</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Draw Date:</span>
                        <span class="font-medium">{{ $bet->draw->draw_date->format('F j, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Draw Time:</span>
                        <span class="font-medium">{{ \Carbon\Carbon::parse($bet->draw->draw_time)->format('g:i A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Teller:</span>
                        <span class="font-medium">{{ $bet->teller->name }}</span>
                    </div>
                    @if($bet->location)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Location:</span>
                            <span class="font-medium">{{ $bet->location->name }}</span>
                        </div>
                    @endif
                    @if($bet->customer)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Customer:</span>
                            <span class="font-medium">{{ $bet->customer->name }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">Bet Date:</span>
                        <span class="font-medium">{{ $bet->bet_date->format('F j, Y g:i A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Created At:</span>
                        <span class="font-medium">{{ $bet->created_at->format('F j, Y g:i A') }}</span>
                    </div>
                    @if($bet->is_claimed)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Claimed:</span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Yes
                            </span>
                        </div>
                        @if($bet->claimed_at)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Claimed At:</span>
                                <span class="font-medium">{{ $bet->claimed_at->format('F j, Y g:i A') }}</span>
                            </div>
                        @endif
                    @else
                        <div class="flex justify-between">
                            <span class="text-gray-500">Claimed:</span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                No
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="text-center text-gray-500">
            <p>Bet not found.</p>
        </div>
    @endif

    <x-filament-actions::modals />
</div>
