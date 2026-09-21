<x-layouts::app :title="$service->name">
    <div class="flex flex-col gap-6">
        <flux:button variant="ghost" size="sm" icon="arrow-left" :href="route('services.index')" wire:navigate class="self-start">
            {{ __('Back to Services') }}
        </flux:button>

        <x-page-header :title="$service->name" :subtitle="$service->url">
            <flux:button variant="ghost" icon="pencil" :href="route('services.edit', $service)" wire:navigate>
                {{ __('Edit') }}
            </flux:button>
            <flux:button variant="primary" icon="plus" :href="route('subscriptions.create')" wire:navigate>
                {{ __('New Subscription') }}
            </flux:button>
        </x-page-header>

        <flux:card>
            @if ($service->subscriptions->isEmpty())
                <flux:callout icon="credit-card" :heading="__('No subscriptions yet')" :text="__('Track a subscription against this service to see its history here.')">
                    <x-slot name="actions">
                        <flux:button :href="route('subscriptions.create')" wire:navigate>{{ __('New Subscription') }}</flux:button>
                    </x-slot>
                </flux:callout>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Start Date') }}</flux:table.column>
                        <flux:table.column>{{ __('End Date') }}</flux:table.column>
                        <flux:table.column>{{ __('Price') }}</flux:table.column>
                        <flux:table.column>{{ __('Billing Cycle') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($service->subscriptions as $subscription)
                            <flux:table.row>
                                <flux:table.cell>{{ $subscription->start_date->format('M j, Y') }}</flux:table.cell>
                                <flux:table.cell>{{ $subscription->end_date?->format('M j, Y') ?? '—' }}</flux:table.cell>
                                <flux:table.cell>
                                    {{ $subscription->price ? \Illuminate\Support\Number::currency($subscription->price) : '—' }}
                                </flux:table.cell>
                                <flux:table.cell>{{ $subscription->billing_cycle->label() }}</flux:table.cell>
                                <flux:table.cell>
                                    @if (! $subscription->end_date || $subscription->end_date->greaterThanOrEqualTo(today()))
                                        <flux:badge color="teal" size="sm">{{ __('Active') }}</flux:badge>
                                    @else
                                        <flux:badge color="red" size="sm">{{ __('Expired') }}</flux:badge>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>
    </div>
</x-layouts::app>
