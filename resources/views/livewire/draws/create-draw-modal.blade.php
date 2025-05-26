<x-filament::modal id="create-draw-modal" width="md" wire:model.defer="open">
    <form wire:submit.prevent="create" class="space-y-4">
        {{ $this->form }}
        <div class="flex gap-2 justify-end">
            <x-filament::button type="submit" color="primary">Save</x-filament::button>
            <x-filament::button type="button" color="secondary" wire:click="$set('open', false)">Cancel</x-filament::button>
        </div>
    </form>
</x-filament::modal>
