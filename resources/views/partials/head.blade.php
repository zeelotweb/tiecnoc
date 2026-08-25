<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'tiecnoc') : config('app.name', 'tiecnoc') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.png" type="image/png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />






<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('meta', {
        title: '',
        description: ''
    });

    window.addEventListener('page-meta', e => {
        Alpine.store('meta').title = e.detail.title;
        Alpine.store('meta').description = e.detail.description;
    });
});
</script>



@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance





    <script>
        // Check for saved theme or system preference before page paints
        if (localStorage.getItem('flux.appearance') === 'dark' || 
            (!localStorage.getItem('flux.appearance') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>