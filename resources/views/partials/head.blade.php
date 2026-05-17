<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'tiecnoc') : config('app.name', 'tiecnoc') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
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



{{-- Load FilePond and your app.js --}}
@push('styles')
<link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>

{{-- Your new app.js will handle FilePond initialization automatically --}}

@endpush




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