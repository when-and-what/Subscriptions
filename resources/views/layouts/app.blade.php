<x-layouts::app.header :title="$title ?? null">
    <flux:main container>
        @if (session('success'))
            <flux:callout variant="success" icon="check-circle" :text="session('success')" class="mb-6" />
        @elseif (session('error'))
            <flux:callout variant="danger" icon="exclamation-circle" :text="session('error')" class="mb-6" />
        @endif

        {{ $slot }}
    </flux:main>
</x-layouts::app.header>
