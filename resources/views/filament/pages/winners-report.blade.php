@push('styles')
    <link rel="stylesheet" href="/print.css" media="print">
@endpush
<x-filament-panels::page>
    <!-- Print script -->
    <script>
        function printDiv(divName) {
            // Clone the report content
            var reportDiv = document.getElementById(divName).cloneNode(true);

            // Remove pagination elements
            var paginationElements = reportDiv.querySelectorAll('.print\\:hidden');
            paginationElements.forEach(function(el) {
                el.parentNode.removeChild(el);
            });

            var originalContents = document.body.innerHTML;
            document.body.innerHTML = reportDiv.innerHTML;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>
    <div id="winners-report" class="w-full max-w-7xl mx-auto px-4 py-6 space-y-6 print:mt-0 print:mb-0">
    <!-- Print Button - Positioned at the top right -->
    <div class="flex justify-end mb-4 print:hidden">
        <button
            onclick="printDiv('winners-report')"
            class="filament-button"
            style="background-color: #f59e0b; color: white; font-weight: bold; padding: 0.75rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); display: inline-flex; align-items: center; gap: 0.5rem; font-size: 1rem;"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Report
        </button>
    </div>

    <!-- Filters -->
    <form wire:submit.prevent class="flex flex-col md:flex-row flex-wrap gap-x-4 gap-y-4 py-2 mb-8 bg-white p-4 rounded-lg shadow items-end justify-between print:hidden">
        <div class="flex-1 min-w-[180px] max-w-xs flex flex-col">
            <label class="mb-1 text-sm font-semibold text-gray-700">Date</label>
            <input type="date" wire:model.live="selectedDate" class="w-full" />
        </div>
        <div class="flex-1 min-w-[180px] max-w-xs flex flex-col">
            <label class="mb-1 text-sm font-semibold text-gray-700">Search Ticket/Bet</label>
            <input type="text" wire:model.live="search" placeholder="Search Ticket or Bet Number" class="w-full" />
        </div>
        <div class="flex-1 min-w-[180px] max-w-xs flex flex-col">
            <label class="mb-1 text-sm font-semibold text-gray-700">Teller</label>
            <select wire:model.live="selectedTeller" class="w-full">
                <option value="">All Tellers</option>
                @foreach (\App\Models\User::where('role', 'teller')->get() as $teller)
                    <option value="{{ $teller->id }}">{{ $teller->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[180px] max-w-xs flex flex-col">
            <label class="mb-1 text-sm font-semibold text-gray-700">Coordinator</label>
            <select wire:model.live="selectedCoordinator" class="w-full">
                <option value="">All Coordinators</option>
                @foreach (\App\Models\User::where('role', 'coordinator')->get() as $coord)
                    <option value="{{ $coord->id }}">{{ $coord->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[180px] max-w-xs flex flex-col">
            <label class="mb-1 text-sm font-semibold text-gray-700">Location</label>
            <select wire:model.live="selectedLocation" class="w-full">
                <option value="">All Locations</option>
                @foreach (\App\Models\Location::all() as $loc)
                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[180px] max-w-xs flex flex-col">
            <label class="mb-1 text-sm font-semibold text-gray-700">Game Type</label>
            <select wire:model.live="selectedGameType" class="w-full">
                <option value="">All Game Types</option>
                @foreach (\App\Models\GameType::all() as $gt)
                    <option value="{{ $gt->code }}">{{ $gt->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[180px] max-w-xs flex flex-col">
            <label class="mb-1 text-sm font-semibold text-gray-700">D4 Sub-Selection</label>
            <select wire:model.live="selectedD4SubSelection" class="w-full">
                <option value="">D4 Sub-Selection</option>
                <option value="S2">S2</option>
                <option value="S3">S3</option>
            </select>
        </div>
        <div class="flex-1 min-w-[180px] max-w-xs flex flex-col">
            <label class="mb-1 text-sm font-semibold text-gray-700">Claimed Status</label>
            <select wire:model.live="selectedClaimedStatus" class="w-full">
                <option value="">All Status</option>
                <option value="1">Claimed</option>
                <option value="0">Unclaimed</option>
            </select>
        </div>
        <div class="flex-1 min-w-[100px] max-w-[100px] flex flex-col">
            <label class="mb-1 text-sm font-semibold text-gray-700">Per Page</label>
            <select wire:model.live="perPage" class="w-full">
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </form>
    <div wire:loading.flex class="w-full justify-center items-center py-6"><span class="text-gray-600 text-lg">Loading...</span></div>

    <!-- Totals (always updated) -->
    <div class="flex flex-wrap gap-8 mb-4 p-4 bg-gray-50 rounded shadow">
        <div><span class="font-bold">Total Winners:</span> {{ $this->winners->total() }}</div>
        <div><span class="font-bold">Total Win Amount:</span> ₱{{ number_format($this->winners->sum('winning_amount'), 2) }}</div>
    </div>

    <!-- Winners Table -->
    <div class="overflow-x-auto w-full print:overflow-visible">
        <table class="w-full min-w-max bg-white border border-gray-200 print:w-full print:min-w-0">
            <thead>
                <tr class="bg-gray-100 text-xs uppercase">
                    <th class="px-2 py-2 border">Ticket ID</th>
                    <th class="px-2 py-2 border">Bet Number</th>
                    <th class="px-2 py-2 border">Amount</th>
                    <th class="px-2 py-2 border">Winning Amount</th>
                    <th class="px-2 py-2 border">Game Type</th>
                    <th class="px-2 py-2 border">D4 Sub</th>
                    <th class="px-2 py-2 border">Draw Date</th>
                    <th class="px-2 py-2 border">Draw Time</th>
                    <th class="px-2 py-2 border">Teller</th>
                    <th class="px-2 py-2 border">Location</th>
                    <th class="px-2 py-2 border">Coordinator</th>
                    <th class="px-2 py-2 border">Claimed</th>
                    <th class="px-2 py-2 border">Claimed At</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->winners as $bet)
                    <tr class="text-sm">
                        <td class="px-2 py-1 border">{{ $bet->ticket_id }}</td>
                        <td class="px-2 py-1 border">{{ $bet->bet_number }}</td>
                        <td class="px-2 py-1 border">₱{{ number_format($bet->amount, 2) }}</td>
                        <td class="px-2 py-1 border">₱{{ number_format($bet->winning_amount, 2) }}</td>
                        <td class="px-2 py-1 border">{{ $bet->gameType->code ?? '' }}</td>
                        <td class="px-2 py-1 border">{{ $bet->d4_sub_selection ?? '-' }}</td>
                        <td class="px-2 py-1 border">{{ $bet->draw->draw_date ?? '' }}</td>
                        <td class="px-2 py-1 border">
    {{ $bet->draw && $bet->draw->draw_time ? \Carbon\Carbon::parse($bet->draw->draw_time)->format('g:i A') : '-' }}
</td>
                        <td class="px-2 py-1 border">{{ $bet->teller->name ?? '' }}</td>
                        <td class="px-2 py-1 border">{{ $bet->location->name ?? '' }}</td>
                        <td class="px-2 py-1 border">{{ $bet->teller->coordinator->name ?? '-' }}</td>
                        <td class="px-2 py-1 border">{{ $bet->is_claimed ? 'Yes' : 'No' }}</td>
                        <td class="px-2 py-1 border">{{ $bet->claimed_at ? \Carbon\Carbon::parse($bet->claimed_at)->format('Y-m-d H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="13" class="text-center py-4">No winners found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $this->winners->links() }}
    </div>
</div>


</x-filament-panels::page>
