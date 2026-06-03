<footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-black">

    <div class="max-w-7xl mx-auto px-6 py-16">

        {{-- BRAND --}}
        <div class="max-w-xl mb-12">
            <h3 class="text-sm font-bold tracking-[0.3em] uppercase mb-4">
                TIECNOC
            </h3>

            <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                Connecting communities through commerce, services, and opportunity.
                A marketplace where businesses, contractors, partners, and customers
                can discover, connect, and grow together.
            </p>
        </div>

        {{-- MAIN FOOTER CONTENT --}}
        <div class="grid gap-10 md:grid-cols-[1fr_320px]">

            {{-- NAVIGATION BLOCK --}}
            <div class="grid grid-cols-2 gap-10">

                {{-- EXPLORE --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-[0.2em] mb-4">
                        Explore
                    </h4>

                    <ul class="space-y-3 text-sm">

                        <li>
                            <a href="{{ route('home') }}"
                               class="text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition">
                                Marketplace
                            </a>
                        </li>

                        <li>
                            <a href="#"
                               class="text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition">
                                Services
                            </a>
                        </li>

                        <li>
                            <a href="#"
                               class="text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition">
                                Contractors
                            </a>
                        </li>

                        <li>
                            <a href="#"
                               class="text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition">
                                Partners
                            </a>
                        </li>

                        <li>
                            <a href="#"
                               class="text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition">
                                Categories
                            </a>
                        </li>

                    </ul>
                </div>

                {{-- COMPANY --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-[0.2em] mb-4">
                        Company
                    </h4>

                    <ul class="space-y-3 text-sm">

                        <li>
                            <a href="#"
                               class="text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition">
                                About
                            </a>
                        </li>

                        <li>
                            <a href="#"
                               class="text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition">
                                Contact
                            </a>
                        </li>

                        <li>
                            <a href="#"
                               class="text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition">
                                Support
                            </a>
                        </li>

                        <li>
                            <a href="#"
                               class="text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition">
                                Privacy Policy
                            </a>
                        </li>

                        <li>
                            <a href="#"
                               class="text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition">
                                Terms of Service
                            </a>
                        </li>

                    </ul>
                </div>

            </div>

            {{-- NEWSLETTER --}}
            <div class="order-first md:order-none">

                <h4 class="text-xs font-bold uppercase tracking-[0.2em] mb-4">
                    Stay Connected
                </h4>

                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
                    Get updates on new products, services, opportunities, and platform announcements.
                </p>

                <form action="#" method="POST"
                      class="flex border border-black dark:border-white">
                    @csrf

                    <input
                        type="email"
                        required
                        placeholder="EMAIL ADDRESS"
                        class="flex-1 bg-transparent px-3 py-2 text-sm focus:outline-none"
                    >

                    <button
                        type="submit"
                        class="px-4 bg-black text-white dark:bg-white dark:text-black font-medium">
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

            <div class="flex items-center gap-6 text-[11px] uppercase tracking-[0.15em] text-zinc-500">

                <a href="#" class="hover:text-black dark:hover:text-white transition">
                    Privacy
                </a>

                <a href="#" class="hover:text-black dark:hover:text-white transition">
                    Terms
                </a>

                <a href="#" class="hover:text-black dark:hover:text-white transition">
                    Cookies
                </a>

            </div>

        </div>

    </div>

</footer>
