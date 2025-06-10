<div>
    <x-admin>
        <div class="">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Bet Ratios</h1>
                <p class="mt-1 text-sm text-gray-600">View and manage bet ratios for your location.</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-1 gap-6 mb-6">
                <!-- Total Bet Ratios -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <h3 class="text-lg font-semibold text-gray-700">Total Bet Ratios</h3>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($ratioStats['total_ratios'] ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{ $this->table }}
                </div>
            </div>
        </div>
    </x-admin>
</div>
