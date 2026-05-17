<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}

        @include('footer')
    </flux:main>







{{-- GLOBAL MODAL SUITE --}}

<flux:modal name="media-modal-admin"        
                class="w-full max-w-xl py-12"
                flyout hidden>
    @livewire('admin.tools.visuals')
</flux:modal>

<flux:modal name="specs-modal-admin"        
                class="w-full max-w-xl py-12"
                flyout hidden>
    @livewire('admin.tools.specs')
</flux:modal>

<flux:modal name="edit-modal-admin"        
                class="w-full max-w-xl py-12"
                flyout hidden>
    @livewire('admin.tools.editor')
</flux:modal>





















{{-- GLOBAL MODAL SUITE --}}



<flux:modal name="media-modal"        
                class="w-full max-w-xl py-12"
                flyout>
    @livewire('admin.tools.visuals')
</flux:modal>

<flux:modal name="specs-modal"        
                class="w-full max-w-xl py-12"
                flyout>
    @livewire('admin.tools.specs')
</flux:modal>

<flux:modal name="edit-modal"        
                class="w-full max-w-xl py-12"
                flyout>
    @livewire('admin.tools.editor')
</flux:modal>

<flux:modal name="metrics-modal"        
                class="w-full max-w-xl py-12"
                flyout>
    @livewire('admin.tools.metrics')
</flux:modal>


<flux:modal name="metrix-global-modal"        
                class="w-full max-w-xl py-12"
                flyout>
    @livewire('admin.tools.metrix_global')
</flux:modal>

<flux:modal name="media-gallery-modal"        
                class="w-full max-w-xl py-12"
                flyout>
    @livewire('admin.tools.gallery')
</flux:modal>


<flux:modal name="load-media-tool"        
                class="w-full max-w-xl py-12"
                flyout>
   @livewire('admin.media.gallery')
</flux:modal>

<flux:modal 
    name="color-toggle-modal"        
    class="w-full max-w-xl py-12"
    flyout
>
    @livewire('admin.tools.color-toggle-modal')
</flux:modal>


</x-layouts::app.sidebar>
