<div>
    <x-admin>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Low Win Numbers</h1>
            <p class="mt-1 text-sm text-gray-600">View and manage low win numbers for your locations.</p>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-admin>
</div>

