<header class="w-full border-b border-black dark:border-white bg-white dark:bg-black p-2 transition-all duration-500 sticky top-0 z-50">
    <div class="flex items-center justify-between max-w-[1440px] mx-auto">
        
        {{-- Left: Logo --}}
        <div class="flex items-center">
            <a href="dashboard" class="group flex items-center gap-1">
                <div class=" p-1">
                    <x-app-logo-icon class="w-5 h-5 invert dark:invert-0" />
                </div>
           
            </a>
        </div>

        {{-- Right: Shopping Bag + Auth Logic --}}
        <div class="flex items-center gap-6">
            @auth

            <div class="flex-col">
                <div class="flex">
                {{-- Authenticated: Direct to Cart Page --}}
                <a href="/cart" class="group flex items-center gap-2 px-2">
                <span class="text-[10px] font-bold uppercase tracking-[0.3em]">Bag</span>
                <div class="relative">
                    <flux:icon.shopping-bag class="w-5 h-5 text-black dark:text-white" />
                    {{-- Cart Count Badge (Optional) --}}
                    <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-black text-[8px] text-white dark:bg-white dark:text-black">
                        {{ auth()->user()->cart_items_count ?? 0 }}
                    </span>
                </div>
                </a>
                <x-appearance />
                </div>

                  <span class="flex"> 
                <flux:avatar name="Sam" class="flex"/>
                    <form method="POST" action="{{ route('logout') }}" class="w-full flex">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="{{-- arrow-right-start-on-rectangle --}}"
                            class="w-full cursor-pointer text-white justify-end"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                 </span>

        </div>
            @else
                {{-- Guest: Trigger Auth Modal --}}
                <div class="flex-col p-2">

                <div class="group flex justify-end gap-2 pr-2 w-auto">
                <button class="flex hover:opacity-50 transition-opacity float-right "
                >
                    <span class="flex items-center text-[10px] font-bold uppercase tracking-[0.3em]">Bag</span>
                    <flux:icon.shopping-bag class="w-5 h-5 text-black dark:text-white" />
                     
                </button>
                <x-appearance />     
                </div>

                <div class="flex">

                <a href="{{ route('login') }}" class="flex bg-black px-2 py-3 text-xs font-bold uppercase tracking-widest text-white transition-all hover:invert dark:bg-white dark:text-black">
                    Log in
                </a>

                @if (Route::has('register'))
                <a href="{{ route('register') }}" class="flex bg-black px-2 py-3 text-xs font-bold uppercase tracking-widest text-white transition-all hover:invert dark:bg-white dark:text-black">
                    Register
                </a>
                @endif

            @endauth
                </div>
                

                </div>

        </div>
    </div>
</header>
