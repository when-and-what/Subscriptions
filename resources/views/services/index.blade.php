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
                        <div class="relative flex items-start justify-between gap-2">
                            <flux:heading size="lg">
                                <a href="{{ route('services.show', $service) }}" wire:navigate class="after:absolute after:inset-0 after:content-['']">
                                    {{ $service->name }}
                                </a>
                            </flux:heading>
                            <div class="flex items-center gap-2">
                                @if (! $service->subscription)
                                    <flux:badge color="zinc" size="sm">{{ __('No subscription') }}</flux:badge>
                                @elseif ($service->is_active)
                                    <flux:badge color="teal" size="sm">
                                        {{ $service->subscription->end_date ? __('Active · :date', ['date' => $service->subscription->end_date->format('M jS')]) : __('Active') }}
                                    </flux:badge>
                                @else
                                    <flux:badge color="red" size="sm">
                                        {{ __('Expired · :date', ['date' => $service->subscription->end_date->format('M jS')]) }}
                                    </flux:badge>
                                @endif
                            </div>
                        </div>

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
