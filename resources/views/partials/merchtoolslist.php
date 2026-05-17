
    <flux:dropdown>
        {{-- The Gothic Trigger Button --}}
        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" class="hover:bg-black hover:text-white transition-all rounded-none" />

        <flux:menu class="min-w-[220px] rounded-none border-black border-2 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] dark:border-white">
                        
            {{-- Visuals Trigger --}}
            <flux:menu.item 
                icon="photo" 
                class="uppercase text-[10px] font-black tracking-widest"
                x-on:click="
                    $dispatch('load-visuals-tool', { id: {{ $product->id }} });
                    $flux.modal('media-modal').show();
                "
            >
                Add Visuals
            </flux:menu.item>

            {{-- Specs Trigger --}}
            <flux:menu.item 
                icon="swatch" 
                class="uppercase text-[10px] font-black tracking-widest"
                x-on:click="
                    $dispatch('load-specs-tool', { id: {{ $product->id }} });
                    $flux.modal('specs-modal').show();
                "
            >
                Manage Specs
            </flux:menu.item>

            {{-- Editor Trigger --}}
            <flux:menu.item 
                icon="pencil-square" 
                class="uppercase text-[10px] font-black tracking-widest"
                x-on:click="$dispatch('load-editor-tool', { id: {{ $product->id }} }); $flux.modal('edit-modal').show();"
                >
                Edit Info
            </flux:menu.item>


        <flux:menu.separator class="bg-black/10 dark:bg-white/10" />

      {{-- Metrix Trigger --}}

<flux:menu.item 
    icon="chart-bar"
    class="uppercase text-[10px] font-black tracking-widest"
    x-on:click="
        $dispatch('load-metrics-tool', { id: {{ $product->id }} });
        $flux.modal('metrics-modal').show();
    "
>
    Metrics
</flux:menu.item>
<flux:menu.item 
    icon="photo"
    class="uppercase text-[10px] font-black tracking-widest"
x-on:click="
    $dispatch('media-gallery-modal', { id: {{ $product->id }} });

    setTimeout(() => {
        window.initProductGalleryPond();
    }, 100);

    $flux.modal('media-gallery-modal').show();
"
>

{{ $product->id }}

    Add Media
</flux:menu.item>
<flux:menu.item 
    icon="photo"
    class="uppercase text-[10px] font-black tracking-widest"
x-on:click="
    $dispatch('load-media-tool', { id: {{ $product->id }} });
    $flux.modal('load-media-tool').show();
"
>



    View Media
</flux:menu.item>

            <flux:menu.separator class="bg-black/10 dark:bg-white/10" />


            
            {{-- 04 / Page Preview --}}
            <flux:menu.item 
                icon="eye" 
                class="group uppercase text-[10px] font-black tracking-[0.2em] py-3"
                href="{{ route('admin.merchandise.show', $product->id) }}"
            >
                View Page
                <flux:spacer />
                <span class="opacity-0 group-hover:opacity-100 transition-opacity italic text-[8px]">
                 VIEW
                </span>
            </flux:menu.item>

            {{-- 05 / Soft Delete --}}
            <flux:menu.item 
                icon="trash" 
                variant="danger"
                class="group uppercase text-[10px] font-black tracking-[0.2em] py-3"
                wire:click="softDelete({{ $product->id }})"
                wire:confirm="MOVE TO TRASH / ARCHIVE?"
            >
                Delete Merch
                <flux:spacer />
                <span class="opacity-0 group-hover:opacity-100 transition-opacity italic text-[8px]">
                DEL
                </span>
            </flux:menu.item>

        </flux:menu>
    </flux:dropdown>

