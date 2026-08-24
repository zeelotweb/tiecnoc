<x-layouts::admin.adminbar :title="$pageTitle ?? $title ?? null">
    <flux:main>
        <div class="max-w-7xl mx-auto p-4 lg:p-10 space-y-10">

            @if(isset($pageTitle))
                <div class="flex flex-wrap items-end justify-between gap-4 border-b border-black dark:border-white pb-6">
                    <div>
                        @if(isset($pageEyebrow))
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#E31837] mb-2">{{ $pageEyebrow }}</p>
                        @endif
                        <h1 class="text-3xl md:text-4xl font-black tracking-tight leading-none">{{ $pageTitle }}</h1>
                    </div>
                    @isset($pageActions)
                        <div class="flex items-center gap-3">{{ $pageActions }}</div>
                    @endisset
                </div>
            @endif

            {{ $slot }}
        </div>
    </flux:main>
</x-layouts::admin.adminbar>
