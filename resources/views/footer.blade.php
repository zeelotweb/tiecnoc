<footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-black">

    <div class="max-w-7xl mx-auto px-6 py-16">

        <div class="grid gap-12 md:grid-cols-4">

            {{-- BRAND --}}
            <div class="space-y-4">

                <h3 class="text-sm font-bold tracking-[0.3em] uppercase">
                    TIECNOC
                </h3>

                <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    {{-- Connecting communities through commerce,
                    services, and opportunity. --}}
                    Elevate your everyday rotation. High-quality essentials designed for versatility, built to last.
                </p>

            </div>

            {{-- EXPLORE --}}
            <div>

                <h4 class="text-xs font-bold uppercase tracking-[0.2em] mb-4">
                    Explore
                </h4>

                <ul class="space-y-2 text-sm">

                    <li>
                        <a href="#" class="hover:underline">
                            Marketplace
                        </a>
                    </li>

                    <li>
                        <a href="#" class="hover:underline">
                            Services
                        </a>
                    </li>

                    <li>
                        <a href="#" class="hover:underline">
                            Contractors
                        </a>
                    </li>

                    <li>
                        <a href="#" class="hover:underline">
                            Partners
                        </a>
                    </li>

                </ul>

            </div>

            {{-- COMPANY --}}
            <div>

                <h4 class="text-xs font-bold uppercase tracking-[0.2em] mb-4">
                    Company
                </h4>

                <ul class="space-y-2 text-sm">

                    <li>
                        <a href="#" class="hover:underline">
                            About
                        </a>
                    </li>

                    <li>
                        <a href="#" class="hover:underline">
                            Contact
                        </a>
                    </li>

                    <li>
                        <a href="#" class="hover:underline">
                            Careers
                        </a>
                    </li>

                </ul>

            </div>

            {{-- NEWSLETTER --}}
            <div>

                <h4 class="text-xs font-bold uppercase tracking-[0.2em] mb-4">
                    Stay Connected
                </h4>

                <form class="flex border border-black dark:border-white">

                    <input
                        type="email"
                        placeholder="EMAIL ADDRESS"
                        class="flex-1 bg-transparent px-3 py-2 text-sm focus:outline-none"
                    >

                    <button
                        type="submit"
                        class="px-4 bg-black text-white dark:bg-white dark:text-black"
                    >
                        Join
                    </button>

                </form>

            </div>

        </div>

    </div>

    {{-- BOTTOM BAR --}}
    <div class="border-t border-zinc-200 dark:border-zinc-800">

        <div class="max-w-7xl mx-auto px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-4">

            <p class="text-[11px] uppercase tracking-[0.15em] text-zinc-500">
                © {{ date('Y') }} Tiecnoc. All Rights Reserved.
            </p>

            <div class="flex gap-6 text-[11px] uppercase tracking-[0.15em]">

                <a href="#">Privacy</a>

                <a href="#">Terms</a>

                <a href="#">Cookies</a>

            </div>

        </div>

    </div>

</footer>
