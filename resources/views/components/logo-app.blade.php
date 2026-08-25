@props(['class' => 'h-14 w-12'])

<span {{ $attributes->merge(['class' => "relative inline-block {$class}"]) }}>
    <img src="{{ asset('images/mark-black.png') }}" alt="Tiecnoc"
         class="absolute inset-0 w-full h-full object-contain dark:hidden">
    <img src="{{ asset('images/mark-white.png') }}" alt="Tiecnoc"
         class="absolute inset-0 w-full h-full object-contain hidden dark:block">
</span>