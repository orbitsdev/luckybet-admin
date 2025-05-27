<x-filament::modal>
    <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">Manage Commission</h2>
        <p>This is a placeholder for managing teller commission settings.</p>
        <div class="mt-4">
            <span class="font-medium">User:</span> {{ $record->name }}<br>
            <span class="font-medium">Current Commission Rate:</span> {{ $record->commission->rate ?? '-' }}%
        </div>
        <div class="mt-6 text-right">
            <x-filament::button color="secondary" wire:click="$dispatch('close-modal')">Close</x-filament::button>
        </div>
    </div>
</x-filament::modal>
