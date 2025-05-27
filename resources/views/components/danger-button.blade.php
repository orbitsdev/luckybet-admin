<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-[#FC0204] to-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:from-red-700 hover:to-[#FC0204] active:bg-[#FC0204] focus:outline-none focus:ring-2 focus:ring-[#FC0204] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
