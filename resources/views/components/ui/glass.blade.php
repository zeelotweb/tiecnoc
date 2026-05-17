<div {{ $attributes->merge([
    'class' => '
        bg-neutral-50 dark:bg-black/60
        backdrop-blur-xs
        border border-black/10 dark:border-white/10
        shadow-xs
        rounded-xl
    '
]) }}>
    {{ $slot }}
</div>