<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-black font-sans text-black dark:text-white antialiased">
    {{-- SIDEBAR --}}
    <flux:sidebar sticky collapsible="mobile" class="h-screen border-e-1 border-black bg-white dark:border-white dark:bg-black p-0 flex flex-col justify-between">
        <div>
            {{-- HEADER --}}
            <flux:sidebar.header class="flex flex-col border-b border-black dark:border-white p-2 gap-3">
                <div class="flex items-center justify-between w-full">
                    <a href="/" class="group flex items-center gap-2">
                        <div class="p-1">
                            <x-app-logo-icon class="w-4 h-4 invert dark:invert-0" />
                        </div>
                    </a>
                </div>
                {{-- BAG (Livewire Source of Truth) --}}
                <div class="flex flex-row items-center justify-between px-2">
                    <span class="flex text-[10px] font-bold uppercase tracking-[0.3em]"> Bag </span>
                    @livewire('store.cart-count')
                    <div class="flex px-2">
                        <x-appearance />
                    </div>
                </div>
            </flux:sidebar.header>

            {{-- NAVIGATION --}}
            <flux:sidebar.nav class="p-3">
                <flux:sidebar.group :heading="__('THE COLLECTIONS')" class="grid">
                    <flux:sidebar.item icon="squares-2x2" :href="route('store.all')" :current="request()->routeIs('store.all')" wire:navigate> All </flux:sidebar.item>
                    <flux:sidebar.item icon="user-plus" :href="route('store.female')" :current="request()->routeIs('store.female')" wire:navigate> Women </flux:sidebar.item>
                    <flux:sidebar.item icon="user" :href="route('store.male')" :current="request()->routeIs('store.male')" wire:navigate> Men </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('store.unisex')" :current="request()->routeIs('store.unisex')" wire:navigate> Unisex </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>
        </div>

        <div class="p-3 w-full space-y-4">
            {{-- ADMIN --}}
            @auth
                <flux:sidebar.group :heading="__('ADMIN MATRIX')">
                    <flux:sidebar.item href="{{ route('admin.dashboard') }}" icon="shield-check" > Account </flux:sidebar.item>
                    <flux:sidebar.item icon="bookmark" :href="route('store.favorites')" :current="request()->routeIs('store.favorites')" wire:navigate> Favorite </flux:sidebar.item>
                </flux:sidebar.group>
            @else
                <flux:sidebar.item icon="user-plus" :href="route('login')" :current="request()->routeIs('login')" wire:navigate> Login </flux:sidebar.item>
            @endauth

            @auth
                @if(auth()->user()->canAccessAdmin())
                    <flux:sidebar.item href="{{ route('admin.dashboard') }}" icon="shield-check" > Admin Portal </flux:sidebar.item>
                @endif
                
                <flux:dropdown position="top" align="start" class="w-full">
                    <flux:profile name="{{ auth()->user()->name }}" class="cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-900 rounded p-1 transition" />
                    
                    <flux:menu class="w-48">
                        <flux:menu.item icon="cog-6-tooth" href="{{ route('admin.dashboard') }}">Settings</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item variant="danger" icon="arrow-right-start-on-rectangle">
                            <form method="POST" action="{{ route('logout') }}" class="w-full m-0 p-0">
                                @csrf
                                <button type="submit" class="w-full text-left bg-transparent border-0 p-0 text-inherit font-inherit cursor-pointer text-xs uppercase italic font-extrabold text-pink-600 dark:text-white">
                                    Logout
                                </button>
                            </form>
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            @endauth

            @guest
                {{-- PARTNER ENTRY --}}
                <flux:sidebar.item icon="user-plus" {{-- :href="route('partner.apply')" :current="request()->routeIs('partner.*')" --}} wire:navigate> Be A Partner </flux:sidebar.item>
                {{-- CONTRACTOR ENTRY --}}
                <flux:sidebar.item icon="wrench" {{-- :href="route('contractor.apply')" :current="request()->routeIs('contractor.*')" --}} wire:navigate> Become Contractor </flux:sidebar.item>
            @endguest
        </div>
    </flux:sidebar>

    {{-- HEADER (mobile stays as previously updated) --}}
    <flux:header class="lg:hidden sticky top-0 w-full border-b border-black dark:border-white bg-white dark:bg-black py-2">
        <div class="flex items-center justify-between w-full mx-auto">
            <div class="flex items-center w-auto">
                <flux:sidebar.toggle class="mr-2" icon="bars-2" inset="left" />
                <a href="/" class="group flex items-center gap-1">
       
                       <x-logo-app class="w-4 h-4" />
                </a>
            </div>
            <div class="flex items-center gap-6">
                @auth
                    <div class="flex flex-col items-end">
                        <div class="flex items-center">
                            <div class="flex items-center gap-2 px-2">
                                <span class="text-[10px] font-bold uppercase tracking-[0.3em]">Bag</span>
                                @livewire('store.cart-count')
                            </div>
                            <x-appearance />
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <flux:avatar name="{{ auth()->user()->name }}" size="xs" hidden/>
                            <span class="text-xs border-r-2 border-black dark:border-white pr-2"> {{ auth()->user()->name }} </span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-xs text-pink-600 dark:text-white uppercase italic opacity-90 bg-neutral-100 dark:bg-transparent p-1 cursor-pointer font-extrabold"> Logout </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-end p-2">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2 px-2">
                                <span class="text-[10px] font-bold uppercase tracking-[0.3em]">Bag</span>
                                @livewire('store.cart-count')
                            </div>
                            <x-appearance />
                        </div>
                        <a href="{{ route('login') }}" class="mt-1 bg-black px-2 py-1 text-[10px] font-bold uppercase text-white dark:bg-white dark:text-black"> Log in </a>
                    </div>
                @endauth
            </div>
        </div>
    </flux:header>

    {{-- MAIN CONTENT --}}
    {{ $slot }}
    @fluxScripts
</body>
</html>
