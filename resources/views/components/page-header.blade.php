@props([
    'title',
    'subtitle' => null,
])

<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <flux:heading size="xl">{{ $title }}</flux:heading>
        @if ($subtitle)
            <flux:text class="mt-1">{{ $subtitle }}</flux:text>
        @endif
    </div>

    @if ($slot->isNotEmpty())
        <div class="flex items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
