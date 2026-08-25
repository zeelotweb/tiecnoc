<button
    type="button"
    x-data
    x-on:click="$flux.appearance = (document.documentElement.classList.contains('dark') ? 'light' : 'dark')"
    class="group relative flex h-6 w-6 items-center justify-center overflow-hidden md:order-last"
    title="Toggle appearance"
    aria-label="Toggle light and dark appearance"
>
    {{-- Sun Icon: Visible when NOT dark --}}
    <flux:icon.sun class="absolute transition-all duration-500 rotate-0 scale-100 dark:-rotate-90 dark:scale-0 text-black" />

    {{-- Moon Icon: Visible when dark --}}
    <flux:icon.moon class="absolute transition-all duration-500 rotate-90 scale-0 dark:rotate-0 dark:scale-100 text-white" />
</button>
