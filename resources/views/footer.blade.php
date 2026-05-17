{{-- Located inside your <flux:sidebar> component block --}}
<flux:sidebar.footer class="flex flex-col gap-6 p-4 border-t border-black dark:border-white bg-white dark:bg-black">
    
    @auth
        {{-- SECTION 1: ADMIN MATRIX --}}
        <flux:sidebar.group :heading="__('ADMIN MATRIX')">
            <flux:sidebar.item href="{{ route('admin.dashboard') }}" icon="shield-check"> Account </flux:sidebar.item>
            <flux:sidebar.item icon="bookmark" :href="route('store.favorites')" :current="request()->routeIs('store.favorites')" wire:navigate> Favorite </flux:sidebar.item>
            @if(auth()->user()->canAccessAdmin())
                <flux:sidebar.item href="{{ route('admin.dashboard') }}" icon="shield-check"> Admin Portal </flux:sidebar.item>
            @endif
        </flux:sidebar.group>

        {{-- SECTION 2: USER PROFILE & SETTINGS DROPDOWN --}}
        <div class="pt-2 border-t border-zinc-100 dark:border-zinc-900">
            <flux:dropdown position="top" align="start" class="w-full">
                <flux:profile name="{{ auth()->user()->name }}" avatar="livewire.dev" class="cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-900 p-2 rounded-lg transition" />
                
                <flux:menu class="w-48">
                    <flux:menu.item icon="cog-6-tooth" href="{{ route('admin.dashboard') }}">Settings</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item variant="danger" icon="arrow-right-start-on-rectangle">
                        <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form" class="w-full h-full m-0 p-0">
                            @csrf
                            <button type="submit" class="w-full text-left bg-transparent border-0 p-0 text-inherit font-inherit cursor-pointer">
                                Logout
                            </button>
                        </form>
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    @else
        {{-- SECTION 1: JOIN THE LIST (GUEST CONVERTOR) --}}
        <div class="space-y-2 px-2">
            <span class="text-[9px] font-bold uppercase tracking-[0.2em] text-zinc-400 block">Join the list</span>
            <form action="#" method="POST" class="flex border border-black dark:border-white">
                @csrf
                <input type="email" required placeholder="EMAIL" 
                    class="w-full bg-transparent px-2 py-1 text-[10px] text-black dark:text-white placeholder-zinc-500 focus:outline-none uppercase font-mono" />
                <button type="submit" 
                    class="bg-black text-white dark:bg-white dark:text-black px-2 py-1 text-[10px] font-bold uppercase tracking-wider hover:opacity-90 transition">
                    +
                </button>
            </form>
        </div>

        {{-- SECTION 2: GUEST GATEWAYS --}}
        <flux:sidebar.group class="pt-2 border-t border-zinc-100 dark:border-zinc-900">
            <flux:sidebar.item icon="user-plus" :href="route('login')" :current="request()->routeIs('login')" wire:navigate> Login </flux:sidebar.item>
            <flux:sidebar.item icon="user-plus" wire:navigate> Be A Partner </flux:sidebar.item>
            <flux:sidebar.item icon="wrench" wire:navigate> Become Contractor </flux:sidebar.item>
        </flux:sidebar.group>
    @endauth

    {{-- SECTION 3: COPYRIGHT SIGNATURE --}}
    <div class="px-2 pt-1">
        <p class="text-[8px] font-mono tracking-widest text-zinc-400 uppercase">
            &copy; {{ date('Y') }} ALL RIGHTS RESERVED.
        </p>
    </div>

</flux:sidebar.footer>
