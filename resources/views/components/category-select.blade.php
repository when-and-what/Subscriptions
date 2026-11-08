@props([
    'selected' => [],
])

@php
    $categories = auth()->user()->categories()->orderBy('name')->get();
    $current = collect(old('categories', $selected))->map(fn ($id) => (string) $id);
@endphp

<flux:field>
    <div class="flex items-center justify-between">
        <flux:label>{{ __('Categories (optional)') }}</flux:label>
        <flux:link href="{{ route('categories.index') }}" wire:navigate>{{ __('Manage categories') }}</flux:link>
    </div>

    @if ($categories->isEmpty())
        <flux:text size="sm" variant="subtle">{{ __('No categories yet.') }}</flux:text>
    @else
        <flux:checkbox.group>
            @foreach ($categories as $category)
                <flux:checkbox
                    name="categories[]"
                    value="{{ $category->id }}"
                    :checked="$current->contains((string) $category->id)"
                    label="{{ $category->name }}"
                />
            @endforeach
        </flux:checkbox.group>
    @endif
</flux:field>
