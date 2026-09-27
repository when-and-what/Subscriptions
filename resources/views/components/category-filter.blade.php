@props([
    'categories',
    'route',
])

@php
    $active = request()->integer('category') ?: null;
@endphp

<div class="flex flex-wrap gap-2">
    <flux:button size="sm" class="rounded-full" variant="{{ $active ? 'ghost' : 'primary' }}" :href="route($route)" wire:navigate>
        {{ __('All') }}
    </flux:button>

    @foreach ($categories as $category)
        <flux:button
            size="sm"
            class="rounded-full"
            variant="{{ $active === $category->id ? 'primary' : 'ghost' }}"
            :href="route($route, ['category' => $category->id])"
            wire:navigate
        >
            {{ $category->name }}
        </flux:button>
    @endforeach
</div>
