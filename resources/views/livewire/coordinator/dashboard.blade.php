<div>
    <x-admin>


    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Coordinator Dashboard</h2>
    
    <!-- Date Filter -->
    <div class="mb-6">
        <label for="date-picker" class="block text-sm font-medium text-gray-700">Date</label>
        <input type="date" wire:model.live="selectedDate" wire:change="loadStats" id="date-picker" class="mt-1 block w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Bets</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($totalBets) }}</dd>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Amount</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">₱{{ number_format($totalAmount, 2) }}</dd>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Winning Amount</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">₱{{ number_format($totalWinningAmount, 2) }}</dd>
            </div>
        </div>
    </div>
    
    <!-- Teller Performance -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Teller Performance</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Performance metrics for tellers under your coordination.</p>
        </div>
        <div class="border-t border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teller</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bets</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Winning Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profit/Loss</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tellerStats as $teller)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $teller->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($teller->bet_count) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($teller->total_amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($teller->total_winning_amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $teller->total_amount - $teller->total_winning_amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ₱{{ number_format($teller->total_amount - $teller->total_winning_amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Game Type Distribution -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Game Type Distribution</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Breakdown of bets by bet type.</p>
        </div>
        <div class="border-t border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bets</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($gameTypeStats as $gameType)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $gameType->name }} ({{ $gameType->code }})</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($gameType->bet_count) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($gameType->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-admin>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set the date input to today's date if it's empty
        const dateInput = document.getElementById('date-picker');
        if (!dateInput.value) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.value = today;
            
            // Dispatch an input event to notify Livewire of the change
            dateInput.dispatchEvent(new Event('input', { bubbles: true }));
            dateInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
</script>
</div>
