<div>
    <x-admin>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Edit Draw</h1>
                <p class="text-gray-500">Manage draw details, bet ratios, low win numbers, and results</p>
            </div>
            <div>
                <x-filament::button
                    tag="a"
                    href="{{ route('manage.draws') }}"
                    icon="heroicon-o-arrow-left"
                    color="gray"
                >
                    Back to Draws
                </x-filament::button>
            </div>
        </div>

       

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form wire:submit="save">
                {{ $this->form }}

                <div class="flex mt-6">
                    <x-filament::button type="submit" class="">
                        Save Changes
                    </x-filament::button>
                </div>
            </form>
        </div>

        <x-filament-actions::modals />
    </x-admin>
</div>
