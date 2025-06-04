
<div>
    <x-admin>
    <form wire:submit="create">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-4">
            SAVE
        </x-filament::button>
    </form>

    <x-filament-actions::modals />
</x-admin>
</div>

