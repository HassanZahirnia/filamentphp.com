@props([
    'danger' => false,
    'link' => false,
    'small' => false,
    'submit' => false,
])

@if ($link)
    <x-filament-support::link
        :type="$submit ? 'submit' : 'button'"
        :color="$danger ? 'danger' : null"
        size="sm"
        :attributes="$attributes"
        class="mx-3"
    >
        {{ $slot }}
    </x-filament-support::link>
@else
    <x-filament-support::button
        :type="$submit ? 'submit' : 'button'"
        :color="$danger ? 'danger' : null"
        size="sm"
        :attributes="$attributes"
    >
        {{ $slot }}
    </x-filament-support::button>
@endif
