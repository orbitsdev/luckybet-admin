<div>
    <x-admin>
        <div class="mb-6">
            <h1 class="text-2xl font-bold">Edit Teller</h1>
            <p class="text-gray-600">Update teller account information and settings</p>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
     
        
        <form wire:submit="update">
            {{ $this->form }}

            <div class="mt-6 flex items-center gap-4">
                <x-filament::button type="submit">
                    Update Teller
                </x-filament::button>
                
                <x-filament::button type="button" color="gray" tag="a" href="{{ route('coordinator.tellers') }}">
                    Cancel
                </x-filament::button>
            </div>
        </form>

        <x-filament-actions::modals />
    </x-admin>
</div>
