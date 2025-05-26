<div>
    <x-admin>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div class="md:col-span-1">
                {{ $this->addDrawAction }}
            </div>
            <div class="md:col-span-2">
                {{ $this->table }}
            </div>
        </div>
        <!-- Right: Table -->
       
        <x-filament-actions::modals />
    </x-admin>
</div>

