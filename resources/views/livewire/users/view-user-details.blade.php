<div class="p-6">
    <div class="flex items-center mb-6">
        <img src="{{ $record->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($record->name) }}" alt="Profile Photo" class="w-20 h-20 rounded-full shadow border mr-6">
        <div>
            <div class="text-2xl font-semibold text-gray-800">{{ $record->name }}</div>
            <div class="text-gray-500">{{ ucfirst($record->role) }}</div>
            <div class="mt-1 text-sm text-gray-400">Joined: {{ $record->created_at->format('M d, Y') }}</div>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <div class="mb-2 text-sm text-gray-500">Username</div>
            <div class="text-lg font-medium text-gray-900">{{ $record->username }}</div>
        </div>
        <div>
            <div class="mb-2 text-sm text-gray-500">Email</div>
            <div class="text-lg font-medium text-gray-900">{{ $record->email }}</div>
        </div>
        <div>
            <div class="mb-2 text-sm text-gray-500">Phone</div>
            <div class="text-lg font-medium text-gray-900">{{ $record->phone }}</div>
        </div>
        <div>
            <div class="mb-2 text-sm text-gray-500">Location</div>
            <div class="text-lg font-medium text-gray-900">{{ optional($record->location)->name ?? '-' }}</div>
        </div>
        <div>
            <div class="mb-2 text-sm text-gray-500">Active Status</div>
            <div class="text-lg font-medium">
                @if($record->is_active)
                    <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-700 rounded">Active</span>
                @else
                    <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 rounded">Inactive</span>
                @endif
            </div>
        </div>
        @if($record->role === 'teller')
            <div>
                <div class="mb-2 text-sm text-gray-500">Commission</div>
                <div class="text-lg font-medium text-gray-900">
                    @php
                        $commissionValue = null;
                        if (is_object($record->commission) && isset($record->commission->value)) {
                            $commissionValue = $record->commission->value;
                        } elseif (isset($record->commission_amount)) {
                            $commissionValue = $record->commission_amount;
                        }
                    @endphp
                    @if(is_numeric($commissionValue))
                        {{ number_format($commissionValue, 2) }} %
                    @else
                        -
                    @endif
                </div>
            </div>
        @endif
        <div>
            <div class="mb-2 text-sm text-gray-500">Last Updated</div>
            <div class="text-lg text-gray-900">{{ $record->updated_at->format('M d, Y h:i A') }}</div>
        </div>
    </div>
</div>
