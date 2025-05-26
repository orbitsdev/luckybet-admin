<div>
    <x-admin>
        <div class="max-w-3xl mx-auto py-8">
            <h2 class="text-2xl font-bold mb-6">Create Draw</h2>
            <form wire:submit.prevent="submit">
                {{ $this->form }}
                <div class="mt-6 flex justify-end">
                    <x-filament::button type="submit" color="primary">Save Draw</x-filament::button>
                    <a href="{{ route('manage.draws') }}" class="ml-2">
                        <x-filament::button type="button" color="secondary">Cancel</x-filament::button>
                    </a>
                </div>
            </form>
            @if(session('success'))
                <div class="mt-4 text-green-600">{{ session('success') }}</div>
            @endif
        </div>
    </x-admin>
</div>
