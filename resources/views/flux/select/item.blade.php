@props(['value', 'label'])

<flux:select.option :value="$value">
    {{ $slot }}
</flux:select.option>
