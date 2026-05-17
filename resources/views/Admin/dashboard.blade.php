<x-layouts::app :title="__('AminDashboard')">
<div class="p-8">
    <flux:header class="flex-col w-full">
        <flux:heading size="xl" class="flex w-full">Tiecnoc Admin Control</flux:heading>
        <flux:subheading size="xl" class="flex w-full">Merchandize Creation Studio</flux:subheading>
    <flux:subheading size="xl" class="flex w-full font-black uppercase">
        {{ auth()->user()->role }}
    </flux:subheading>
    </flux:header>



<div class="w-full flex">


    @if(auth()->user()->isSuperAdmin())

        <flux:modal.trigger 
                name="super-admin-control-modal"
                icon="shield-check">
            <flux:button
                icon="shield-check"
                class="flex bg-black text-white dark:bg-white dark:text-black mx-1"
            >
                Control Panel
            </flux:button>
        </flux:modal.trigger>

    @endif






@php $user = auth()->user(); @endphp

@if($user->canAccessAdmin() && in_array($user->role, ['admin', 'super_admin']))
    <flux:modal.trigger name="delegate-work-modal">
        <flux:button
            icon="briefcase"
            class="flex bg-black text-white dark:bg-white dark:text-black mx-1"
        >
            Delegate Work
        </flux:button>
    </flux:modal.trigger>
@endif

</div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
        
        {{-- Tool 1: Add Merchandise --}}
@php $user = auth()->user(); @endphp

@if($user->isAdmin())
    
    {{-- Tool 1: Add Merchandise --}}
    <flux:modal.trigger name="add-merch">
        <flux:card class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors">
            <flux:icon.plus class="mb-4" />
            <flux:heading>Add Merchandise</flux:heading>
            <flux:text>Launch the product creation engine.</flux:text>
        </flux:card>
    </flux:modal.trigger>

    {{-- Tool 3: Invoice Control --}}
    <flux:modal.trigger name="view-invoice">
        <flux:card class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors">
            <flux:icon.currency-dollar class="mb-4" />
            <flux:heading>Invoice</flux:heading>
            <flux:text>Quick look at sales made.</flux:text>
        </flux:card>
    </flux:modal.trigger>

@endif




        {{-- Tool 2: Inventory Control (Placeholder) --}}
        <flux:modal.trigger name="metrix-global-modal">
             <flux:card class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors">
                <flux:icon.archive-box class="mb-4" />
                <flux:heading>Stock Levels</flux:heading>
                <flux:text>Quick restock and SKU management.</flux:text>
            </flux:card>
        </flux:modal.trigger>
    </div>

<div class="w-full mt-3 p-2">
	@livewire('admin.dashboard.merch-snapshot')
</div>
    {{-- The Modal Containers --}}
    <flux:modal name="add-merch"       
    			class="w-full max-w-xl py-12"
        		flyout>
        @livewire('admin.merchandise.create')
    </flux:modal>

    <flux:modal name="manage-inventory"       
    			class="w-full max-w-xl py-12"
        		flyout>
        {{-- You can drop the variants-manager Volt component here --}}
        @livewire('admin.merchandise.inventory')
    </flux:modal>

    <flux:modal name="view-invoice"       
                class="w-full max-w-xl py-12"
                flyout>
        {{-- You can drop the variants-manager Volt component here --}}
        @livewire('admin.dashboard.invoice')
    </flux:modal>





@auth
    @if(auth()->user()->isSuperAdmin())

        <flux:modal 
        name="super-admin-control-modal" 
        class="w-full max-w-xl py-12 px-2"
        flyout>

            <div class="flex items-center justify-center bg-white/95 dark:bg-black/95 backdrop-blur-md">

                <div class="w-full max-w-4xl p-8 border border-black dark:border-white bg-white dark:bg-[#0a0a0a]">

                    {{-- HEADER --}}
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xs uppercase tracking-[0.4em] font-black">
                            Super Admin Control
                        </h2>

                    </div>

                    {{-- CONTENT --}}
                    <div class="max-h-[70vh] overflow-y-auto">
                        <livewire:admin.super-admin-control />
                    </div>

                </div>
            </div>

        </flux:modal>

    @endif
@endauth


@auth
@if($user->canAccessAdmin() && in_array($user->role, ['admin', 'super_admin']))
        <flux:modal name="delegate-work-modal" 
                    class="w-full max-w-xl py-12 px-2"
                    flyout>

            <div class="flex items-center justify-center bg-white/95 dark:bg-black/95 backdrop-blur-md">

                <div class="w-full max-w-5xl p-8 border border-black dark:border-white bg-white dark:bg-[#0a0a0a]">

                    {{-- HEADER --}}
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xs uppercase tracking-[0.4em] font-black">
                            Delegate Duties
                        </h2>

                    </div>

                    {{-- CONTENT --}}
                    <div class="max-h-[75vh] overflow-y-auto">
                        <livewire:admin.delegate-work />
                    </div>

                </div>
            </div>

        </flux:modal>
    @endif
@endauth
</div>
</x-layouts::app>