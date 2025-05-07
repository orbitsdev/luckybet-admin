<x-filament-panels::page>
    <div class="p-6 space-y-4 w-full">
        <div class="flex items-center justify-between">
       

            <div class="flex items-center space-x-2">
                <label for="selectedDate" class="text-sm font-medium">Select Date:</label>
                <input
                    wire:model="selectedDate"
                    type="date"
                    id="selectedDate"
                    class="border rounded px-2 py-1 text-sm"
                />
            </div>
        </div>

        <table class="w-full divide-y divide-gray-200 border text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Draw Time</th>
                    <th class="px-4 py-2 text-left">Teller</th>
                    <th class="px-4 py-2 text-left">S2</th>
                    <th class="px-4 py-2 text-left">S3</th>
                    <th class="px-4 py-2 text-left">D4</th>
                    <th class="px-4 py-2 text-left">Total Sales</th>
                    <th class="px-4 py-2 text-left">Total Hits</th>
                    <th class="px-4 py-2 text-left">Gross</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->summary as $draw)
                    @if (!empty($draw['tellers']))
                        @foreach ($draw['tellers'] as $teller)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ \Carbon\Carbon::createFromFormat('H:i:s', $draw['draw_time'])->format('g:i A') }}</td>
                                <td class="px-4 py-2">{{ $teller['teller_name'] }}</td>
                                <td class="px-4 py-2">{{ $draw['s2_result'] ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $draw['s3_result'] ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $draw['d4_result'] ?? '-' }}</td>
                                <td class="px-4 py-2 font-medium">₱{{ number_format($teller['total_sales'], 2) }}</td>
                                <td class="px-4 py-2 font-medium">₱{{ number_format($teller['total_hits'], 2) }}</td>
                                <td class="px-4 py-2 font-semibold text-blue-600">₱{{ number_format($teller['gross'], 2) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="text-center py-4">No teller records for this draw.</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">No records found for selected date.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
