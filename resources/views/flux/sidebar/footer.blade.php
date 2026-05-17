{{-- footer.blade.php --}}
<footer class="w-full bg-white dark:bg-black border-t border-black dark:border-white transition-colors duration-200">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        
        {{-- Main Grid Section --}}
        <div class="xl:grid xl:grid-cols-3 xl:gap-8 pb-8 border-b border-zinc-200 dark:border-zinc-800">
            
            {{-- Brand and Newsletter Column --}}
            <div class="space-y-6 xl:col-span-1">
                <a href="/" class="flex items-center gap-2">
                    <x-app-logo-icon class="w-6 h-6 invert dark:invert-0" />
                    <span class="text-sm font-bold tracking-[0.3em] uppercase">THE STORE</span>
                </a>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-sm leading-relaxed">
                    Elevate your everyday rotation. High-quality essentials designed for versatility, built to last.
                </p>
                
                {{-- Newsletter Signup --}}
                <div class="space-y-2">
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] block">Join the list</span>
                    <form action="#" method="POST" class="flex max-w-sm border border-black dark:border-white">
                        @csrf
                        <input type="email" required placeholder="Enter your email" 
                            class="w-full bg-transparent px-3 py-2 text-xs text-black dark:text-white placeholder-zinc-400 focus:outline-none" />
                        <button type="submit" 
                            class="bg-black text-white dark:bg-white dark:text-black px-4 py-2 text-xs font-bold uppercase tracking-wider hover:opacity-90 transition">
                            Join
                        </button>
                    </form>
                </div>
            </div>

            {{-- Link Columns --}}
            <div class="mt-12 grid grid-cols-2 gap-8 xl:col-span-2 xl:mt-0 sm:grid-cols-3">
                {{-- Column 1: Shop --}}
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-zinc-400">Shop</span>
                    <ul role="list" class="mt-4 space-y-2">
                        <li><a href="{{ route('store.all') }}" class="text-xs text-black dark:text-white hover:underline uppercase tracking-wider" wire:navigate>All Collections</a></li>
                        <li><a href="{{ route('store.female') }}" class="text-xs text-black dark:text-white hover:underline uppercase tracking-wider" wire:navigate>Women</a></li>
                        <li><a href="{{ route('store.male') }}" class="text-xs text-black dark:text-white hover:underline uppercase tracking-wider" wire:navigate>Men</a></li>
                        <li><a href="{{ route('store.unisex') }}" class="text-xs text-black dark:text-white hover:underline uppercase tracking-wider" wire:navigate>Unisex</a></li>
                    </ul>
                </div>

                {{-- Column 2: Legal / Terms --}}
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-zinc-400">Legal</span>
                    <ul role="list" class="mt-4 space-y-2">
                        <li><a href="#" class="text-xs text-black dark:text-white hover:underline uppercase tracking-wider">Privacy Policy</a></li>
                        <li><a href="#" class="text-xs text-black dark:text-white hover:underline uppercase tracking-wider">Terms of Service</a></li>
                        <li><a href="#" class="text-xs text-black dark:text-white hover:underline uppercase tracking-wider">Returns & Exchanges</a></li>
                    </ul>
                </div>

                {{-- Column 3: Corporate --}}
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-zinc-400">Partnership</span>
                    <ul role="list" class="mt-4 space-y-2">
                        <li><a href="#" class="text-xs text-black dark:text-white hover:underline uppercase tracking-wider">Be A Partner</a></li>
                        <li><a href="#" class="text-xs text-black dark:text-white hover:underline uppercase tracking-wider">Become Contractor</a></li>
                        <li><a href="#" class="text-xs text-black dark:text-white hover:underline uppercase tracking-wider">Contact Support</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Bottom Meta Row --}}
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-[10px] uppercase tracking-widest text-zinc-500 dark:text-zinc-400">
                &copy; {{ date('Y') }} THE STORE. All rights reserved.
            </p>
            
            {{-- Minimalist Payment Icon Placeholders --}}
            <div class="flex items-center gap-2 opacity-40 dark:opacity-60">
                <span class="border border-black dark:border-white px-1.5 py-0.5 text-[8px] font-mono tracking-tighter uppercase rounded-sm">Visa</span>
                <span class="border border-black dark:border-white px-1.5 py-0.5 text-[8px] font-mono tracking-tighter uppercase rounded-sm">Amex</span>
                <span class="border border-black dark:border-white px-1.5 py-0.5 text-[8px] font-mono tracking-tighter uppercase rounded-sm">Master</span>
                <span class="border border-black dark:border-white px-1.5 py-0.5 text-[8px] font-mono tracking-tighter uppercase rounded-sm">ApplePay</span>
            </div>
        </div>
        
    </div>
</footer>
