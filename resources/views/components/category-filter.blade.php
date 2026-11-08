@props([
    'categories',
    'route',
])

@php
    $active = request()->integer('category') ?: null;
    $query = request()->except('page', 'category');
@endphp

<div class="flex flex-wrap gap-2">
    <flux:button size="sm" class="rounded-full" variant="{{ $active ? 'ghost' : 'primary' }}" :href="route($route, $query)" wire:navigate>
        {{ __('All categories') }}
    </flux:button>

    @foreach ($categories as $category)
        <flux:button
            size="sm"
            class="rounded-full"
            variant="{{ $active === $category->id ? 'primary' : 'ghost' }}"
            :href="route($route, [...$query, 'category' => $category->id])"
            wire:navigate
        >
            {{ $category->name }}
        </flux:button>
    @endforeach
</div>
