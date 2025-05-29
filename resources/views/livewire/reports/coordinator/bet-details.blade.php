<div class="p-4">
    @if($bet)
        @if($bet->ticket_id)
            <div class="mb-4 text-center">
                <div class="inline-block bg-white p-4 rounded-md border border-gray-200">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($bet->ticket_id) }}" 
                         alt="Ticket QR Code" class="mx-auto mb-2">
                    <p class="text-sm text-gray-600">Ticket ID: <span class="font-medium">{{ $bet->ticket_id }}</span></p>
                </div>
            </div>
        @endif
        <!-- Bet Information Section -->
        <div class="mb-4">
            <!-- Basic Bet Information -->
                <h3 class="text-lg font-medium text-gray-800 mb-3 border-b pb-2">Bet Information</h3>
                <div class="space-y-2">
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Ticket ID:</span>
                        <span class="font-medium text-blue-600">{{ $bet->ticket_id ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Bet Number:</span>
                        <span class="font-medium">{{ $bet->bet_number }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Game Type:</span>
                        <span class="font-medium">
                            {{ $bet->gameType->name }}
                            @if($bet->d4_sub_selection)
                                <span class="text-xs text-gray-500">({{ $bet->d4_sub_selection }})</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Amount:</span>
                        <span class="font-medium text-blue-600">₱ {{ number_format($bet->amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Winning Amount:</span>
                        <span class="font-medium {{ $bet->winning_amount > 0 ? 'text-red-600' : 'text-gray-500' }}">
                            {{ $bet->winning_amount > 0 ? '₱ ' . number_format($bet->winning_amount, 2) : '-' }}
                        </span>
                    </div>
                    @php $grossAmount = $bet->amount - $bet->winning_amount; @endphp
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Gross:</span>
                        <span class="font-medium {{ $grossAmount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ₱ {{ number_format($grossAmount, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Commission Rate:</span>
                        <span class="font-medium text-purple-600">
                            {{ $bet->commission_rate ? number_format($bet->commission_rate * 100, 0) . '%' : '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Commission Amount:</span>
                        <span class="font-medium text-purple-600">
                            {{ $bet->commission_amount ? '₱ ' . number_format($bet->commission_amount, 2) : '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Status:</span>
                        @if($bet->is_rejected)
                            <span class="px-2 py-0.5 text-xs font-medium rounded bg-red-100 text-red-800">Rejected</span>
                        @elseif($bet->winning_amount > 0)
                            <span class="px-2 py-0.5 text-xs font-medium rounded bg-green-100 text-green-800">Winner</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-medium rounded bg-blue-100 text-blue-800">Placed</span>
                        @endif
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Is Combination:</span>
                        <span class="px-2 py-0.5 text-xs font-medium rounded {{ $bet->is_combination ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $bet->is_combination ? 'Yes' : 'No' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Draw and User Information -->
            <div class="mt-4">
                <h3 class="text-lg font-medium text-gray-800 mb-3 border-b pb-2">Draw & User Information</h3>
                <div class="space-y-2">
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Draw Date:</span>
                        <span class="font-medium">{{ $bet->draw->draw_date->format('F j, Y') }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Draw Time:</span>
                        <span class="font-medium">{{ \Carbon\Carbon::parse($bet->draw->draw_time)->format('g:i A') }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Teller:</span>
                        <span class="font-medium text-blue-600">{{ $bet->teller->name }}</span>
                    </div>
                    
                    @if($bet->location)
                        <div class="flex justify-between py-1">
                            <span class="text-gray-600">Location:</span>
                            <span class="font-medium">{{ $bet->location->name }}</span>
                        </div>
                    @endif
                    
                    @if($bet->customer)
                        <div class="flex justify-between py-1">
                            <span class="text-gray-600">Customer:</span>
                            <span class="font-medium">{{ $bet->customer->name }}</span>
                        </div>
                    @endif
                    
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Bet Date:</span>
                        <span class="font-medium">{{ $bet->bet_date->format('F j, Y g:i A') }}</span>
                    </div>
                    
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Created At:</span>
                        <span class="font-medium">{{ $bet->created_at->format('F j, Y g:i A') }}</span>
                    </div>
                    
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Claimed:</span>
                        <span class="px-2 py-0.5 text-xs font-medium rounded {{ $bet->is_claimed ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $bet->is_claimed ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    
                    @if($bet->is_claimed && $bet->claimed_at)
                        <div class="flex justify-between py-1">
                            <span class="text-gray-600">Claimed At:</span>
                            <span class="font-medium">{{ $bet->claimed_at->format('F j, Y g:i A') }}</span>
                        </div>
                        @if($bet->claimed_at)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Claimed At:</span>
                                <span class="font-medium">{{ $bet->claimed_at->format('F j, Y g:i A') }}</span>
                            </div>
                        @endif
                  
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="text-center text-gray-500">
            <p>Bet not found.</p>
        </div>
    @endif

    
</div>
