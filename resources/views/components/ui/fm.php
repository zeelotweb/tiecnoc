
<flux:modal 
    name="auth-modal" 
    :show="false" 
    focusable
    class="!p-0 !bg-transparent"
>

    <div 
        x-data="{ view: 'login' }"
        class="fixed inset-0 z-[999] flex items-center justify-center bg-white/95 dark:bg-black/95 backdrop-blur-md"
    >

        <div class="w-full max-w-md p-8 border border-black dark:border-white bg-white dark:bg-[#0a0a0a]">

            {{-- HEADER --}}
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xs uppercase tracking-[0.4em] font-black">
                    Access Required
                </h2>

                <button 
                    x-on:click="$flux.modal('auth-modal').hide()"
                    class="text-xs opacity-40 hover:opacity-100"
                >
                    ✕
                </button>
            </div>

            {{-- TABS --}}
            <div class="flex gap-8 border-b border-zinc-200 dark:border-zinc-800 pb-4 mb-6">
                <button 
                    @click="view = 'login'" 
                    :class="view === 'login' ? 'opacity-100 underline underline-offset-8' : 'opacity-40'"
                    class="text-[10px] font-bold uppercase tracking-[0.3em]"
                >
                    Login
                </button>

                <button 
                    @click="view = 'register'" 
                    :class="view === 'register' ? 'opacity-100 underline underline-offset-8' : 'opacity-40'"
                    class="text-[10px] font-bold uppercase tracking-[0.3em]"
                >
                    Register
                </button>
            </div>

            {{-- CONTENT --}}
            <div x-show="view === 'login'">
                @include('pages.auth.login')
            </div>

            <div x-show="view === 'register'">
                @include('pages.auth.register')
            </div>

            <div x-show="view === 'forgot-password'">
                @include('pages.auth.forgot-password')
            </div>

        </div>

    </div>
</flux:modal>