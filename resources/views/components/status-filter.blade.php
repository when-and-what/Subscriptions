@props([
    'status',
    'route',
])

@php
    $query = request()->except('page', 'status');
    $options = [
        'active' => __('Active'),
        'expired' => __('Expired'),
        'all' => __('All'),
    ];
@endphp

<flux:button.group>
    @foreach ($options as $value => $label)
        <flux:button
            size="sm"
            variant="{{ $status === $value ? 'primary' : 'outline' }}"
            :href="route($route, $value === 'active' ? $query : [...$query, 'status' => $value])"
            wire:navigate
        >
            {{ $label }}
        </flux:button>
    @endforeach
</flux:button.group>
