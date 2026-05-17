<!DOCTYPE html>
<meta name="csrf-token" content="{{ csrf_token() }}">
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Welcome') }} - {{ config('app.name', 'LGD') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles -->



    <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
          @fluxAppearance







    </head>





<body class="bg-[#FFFFFF] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-white min-h-screen flex flex-col transition-colors duration-500">
    

    {{-- High-End Navigation --}}
    <x-platform.header />

    {{-- Main Content Area --}}
    <main class="flex-grow flex flex-col w-full transition-opacity opacity-100 duration-750 starting:opacity-0">
        <!--livewire:platform.home /-->
    </main>

    {{-- Sticky/Floating Login Trigger (Optional - Tommy usually hides this in a 'My Account' icon) --}}
    @if (Route::has('login'))
        <div class="fixed bottom-8 right-8 z-40 hidden lg:block">
            <a href="{{ route('login') }}" class="text-[10px] font-bold uppercase tracking-[0.5em] opacity-30 hover:opacity-100 transition-opacity">
                Internal Access
            </a>
        </div>
    @endif



    @fluxScripts
</body>












{{-- Wrap your existing auth partials in this clean container --}}
<x-modal name="auth-modal" :show="false" focusable>
    <div class="p-8 bg-white dark:bg-[#0a0a0a] border border-black dark:border-white">
        <div x-data="{ view: 'login' }" class="flex flex-col gap-6">
            
            {{-- Simple Tab Switcher --}}
            <div class="flex gap-8 border-b border-zinc-200 dark:border-zinc-800 pb-4">
                <button @click="view = 'login'" :class="view === 'login' ? 'opacity-100 underline underline-offset-8' : 'opacity-40'" class="text-[10px] font-bold uppercase tracking-[0.3em]">Login</button>
                <button @click="view = 'register'" :class="view === 'register' ? 'opacity-100 underline underline-offset-8' : 'opacity-40'" class="text-[10px] font-bold uppercase tracking-[0.3em]">Register</button>
            </div>

            {{-- Your Existing Files Injected Here --}}
            <div x-show="view === 'login'">
                @include('pages.auth.login') {{-- Ensure this file only contains the form fields/logic --}}
            </div>
            
            <div x-show="view === 'register'">
                @include('pages.auth.register')
            </div>

            <div x-show="view === 'forgot-password'">
                @include('pages.auth.forgot-password')
            </div>
        </div>
    </div>
</x-modal>

</html>
