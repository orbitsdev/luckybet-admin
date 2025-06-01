<div class="p-4">
    @if($draw->lowWinNumbers->isEmpty())
        <div class="text-center py-8">
            <div class="text-gray-400 mb-2">
                <x-heroicon-o-exclamation-circle class="h-12 w-12 mx-auto" />
            </div>
            <h3 class="text-lg font-medium text-gray-900">No Low Win Numbers Found</h3>
            <p class="mt-1 text-sm text-gray-500">This draw doesn't have any low win numbers configured yet.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Winning Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($draw->lowWinNumbers as $lowWinNumber)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $lowWinNumber->gameType->code ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $lowWinNumber->bet_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($lowWinNumber->winning_amount) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-right text-xs text-gray-500">
            Total: {{ $draw->lowWinNumbers->count() }} low win numbers
        </div>
    @endif
</div>
