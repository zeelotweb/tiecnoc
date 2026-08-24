<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body data-admin-shell class="min-h-screen bg-white dark:bg-black font-sans text-black dark:text-white antialiased">

    {{-- ADMIN SIDEBAR — dedicated shell, not the storefront nav --}}
    <flux:sidebar sticky collapsible="mobile" class="h-screen border-e border-black dark:border-white bg-white dark:bg-black p-0 flex flex-col justify-between">
        <div>
            <flux:sidebar.header class="flex flex-col border-b border-black dark:border-white p-4 gap-1">
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-2">
                    <x-app-logo-icon class="w-4 h-4 invert dark:invert-0" />
                    <span class="text-sm font-black uppercase tracking-[0.2em]">Studio</span>
                </a>
                <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-[#E31837]">{{ str_replace('_', ' ', auth()->user()->role) }}</span>
            </flux:sidebar.header>

            <flux:sidebar.nav class="p-3">
                <flux:sidebar.group class="grid">
                    <flux:sidebar.item icon="squares-2x2" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate class="uppercase text-xs tracking-widest font-medium">Overview</flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Merch Tools')" class="grid [&_[data-flux-heading]]:text-[9px] [&_[data-flux-heading]]:font-bold [&_[data-flux-heading]]:uppercase [&_[data-flux-heading]]:tracking-[0.2em] [&_[data-flux-heading]]:opacity-40">
                    <flux:sidebar.item icon="shopping-bag" :href="route('admin.merchandise.index')" :current="request()->routeIs('admin.merchandise.*')" wire:navigate class="uppercase text-xs tracking-widest font-medium">Products</flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Sales')" class="grid [&_[data-flux-heading]]:text-[9px] [&_[data-flux-heading]]:font-bold [&_[data-flux-heading]]:uppercase [&_[data-flux-heading]]:tracking-[0.2em] [&_[data-flux-heading]]:opacity-40">
                    <flux:sidebar.item icon="receipt-percent" :href="route('admin.orders')" :current="request()->routeIs('admin.orders')" wire:navigate class="uppercase text-xs tracking-widest font-medium">Orders</flux:sidebar.item>
                    <flux:sidebar.item icon="chart-bar" :href="route('admin.reports')" :current="request()->routeIs('admin.reports')" wire:navigate class="uppercase text-xs tracking-widest font-medium">Reports</flux:sidebar.item>
                </flux:sidebar.group>

                @if(auth()->user()->isAdmin())
                    <flux:sidebar.group :heading="__('Network')" class="grid [&_[data-flux-heading]]:text-[9px] [&_[data-flux-heading]]:font-bold [&_[data-flux-heading]]:uppercase [&_[data-flux-heading]]:tracking-[0.2em] [&_[data-flux-heading]]:opacity-40">
                        <flux:sidebar.item icon="building-storefront" :href="route('admin.partners')" :current="request()->routeIs('admin.partners')" wire:navigate class="uppercase text-xs tracking-widest font-medium">Partners</flux:sidebar.item>
                        <flux:sidebar.item icon="truck" :href="route('admin.vendors')" :current="request()->routeIs('admin.vendors')" wire:navigate class="uppercase text-xs tracking-widest font-medium">Vendors</flux:sidebar.item>
                    </flux:sidebar.group>

                    <flux:sidebar.group :heading="__('Staff Management')" class="grid [&_[data-flux-heading]]:text-[9px] [&_[data-flux-heading]]:font-bold [&_[data-flux-heading]]:uppercase [&_[data-flux-heading]]:tracking-[0.2em] [&_[data-flux-heading]]:opacity-40">
                        <flux:sidebar.item icon="user-group" :href="route('admin.team')" :current="request()->routeIs('admin.team')" wire:navigate class="uppercase text-xs tracking-widest font-medium">Crew</flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>
        </div>

        <div class="p-3 w-full space-y-3">
            <flux:sidebar.item icon="arrow-left-start-on-rectangle" href="{{ route('home') }}" wire:navigate class="uppercase text-xs tracking-widest font-medium">Back to Storefront</flux:sidebar.item>

            <flux:dropdown position="top" align="start" class="w-full">
                <flux:profile name="{{ auth()->user()->name }}" class="cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-900 p-1 transition" />

                <flux:menu class="w-48">
                    <flux:menu.item icon="cog-6-tooth" :href="route('profile.edit')" wire:navigate>Settings</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item variant="danger" icon="arrow-right-start-on-rectangle">
                        <form method="POST" action="{{ route('logout') }}" class="w-full m-0 p-0">
                            @csrf
                            <button type="submit" class="w-full text-left bg-transparent border-0 p-0 text-inherit font-inherit cursor-pointer">
                                Logout
                            </button>
                        </form>
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </flux:sidebar>

    {{-- MOBILE HEADER --}}
    <flux:header class="lg:hidden sticky top-0 w-full border-b border-black dark:border-white bg-white dark:bg-black py-2">
        <div class="flex items-center justify-between w-full mx-auto px-2">
            <div class="flex items-center w-auto">
                <flux:sidebar.toggle class="mr-2" icon="bars-2" inset="left" />
                <span class="text-sm font-black uppercase tracking-[0.2em]">Studio</span>
            </div>
            <a href="{{ route('home') }}" wire:navigate class="text-[10px] font-bold uppercase tracking-[0.15em] opacity-60">Storefront</a>
        </div>
    </flux:header>

    {{-- MAIN --}}
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

    @fluxScripts
</body>
</html>
