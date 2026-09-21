<x-layouts::app :title="__('Services')">
    <div class="flex flex-col gap-6">
        <x-page-header :title="__('Services')" :subtitle="__(':count total', ['count' => $services->total()])">
            <flux:button variant="primary" icon="plus" :href="route('services.create')" wire:navigate>
                {{ __('New Service') }}
            </flux:button>
        </x-page-header>

        @if ($services->isEmpty())
            <flux:callout icon="building-storefront" :heading="__('No services yet')" :text="__('Add the subscription providers you want to track, like Netflix or Spotify.')">
                <x-slot name="actions">
                    <flux:button :href="route('services.create')" wire:navigate>{{ __('New Service') }}</flux:button>
                </x-slot>
            </flux:callout>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <flux:card class="flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <flux:heading size="lg">{{ $service->name }}</flux:heading>
                            <flux:badge color="zinc" size="sm">
                                {{ $service->subscriptions_count }} {{ Str::plural('subscription', $service->subscriptions_count) }}
                            </flux:badge>
                        </div>

                        @if ($service->url)
                            <flux:text class="truncate">{{ $service->url }}</flux:text>
                        @endif

                        <div class="mt-2 flex items-center gap-2">
                            <flux:button variant="ghost" size="sm" icon="pencil" :href="route('services.edit', $service)" wire:navigate>
                                {{ __('Edit') }}
                            </flux:button>
                        </div>
                    </flux:card>
                @endforeach
            </div>

            <flux:pagination :paginator="$services" />
        @endif
    </div>
</x-layouts::app>
