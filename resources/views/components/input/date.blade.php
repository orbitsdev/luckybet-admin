@props(['id' => null, 'error' => null])

<div>
    <input 
        type="date" 
        {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full' . ($error ? ' border-red-500' : '')]) }}
        {{ $attributes }}
    />
    @if ($error)
        <p class="mt-1 text-sm text-red-500">{{ $error }}</p>
    @endif
</div>
