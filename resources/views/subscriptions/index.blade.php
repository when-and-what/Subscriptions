<x-layouts::app :title="__('Subscriptions')">
    <div class="flex flex-col gap-6">
        <x-page-header :title="__('Subscriptions')" :subtitle="__(':count total', ['count' => $subscriptions->total()])">
            <flux:button variant="primary" icon="plus" :href="route('subscriptions.create')" wire:navigate>
                {{ __('New Subscription') }}
            </flux:button>
        </x-page-header>

        @if ($subscriptions->isEmpty())
            <flux:callout icon="credit-card" :heading="__('No subscriptions yet')" :text="__('Track a subscription against one of your services to see it here.')">
                <x-slot name="actions">
                    <flux:button :href="route('subscriptions.create')" wire:navigate>{{ __('New Subscription') }}</flux:button>
                </x-slot>
            </flux:callout>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($subscriptions as $subscription)
                    <flux:card class="flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <flux:heading size="lg">{{ $subscription->service->name }}</flux:heading>
                            @if ($subscription->auto_renew)
                                <flux:badge color="teal" size="sm">{{ __('Auto-renews') }}</flux:badge>
                            @endif
                        </div>

                        <div class="flex flex-col gap-1">
                            <flux:text>
                                @if ($subscription->price)
                                    {{ \Illuminate\Support\Number::currency($subscription->price) }} / {{ Str::lower($subscription->billing_cycle->label()) }}
                                @else
                                    {{ $subscription->billing_cycle->label() }}
                                @endif
                            </flux:text>

                            @if ($subscription->end_date)
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                    @if($subscription->auto_renew)
                                        {{ __('Renews :date', ['date' => $subscription->end_date->format('M j, Y')]) }}
                                    @else
                                        {{ __('Expires :date', ['date' => $subscription->end_date->format('M j, Y')]) }}
                                    @endif
                                </flux:text>
                            @endif
                        </div>

                        <div class="mt-2 flex items-center gap-2">
                            <flux:button variant="ghost" size="sm" icon="pencil" :href="route('subscriptions.edit', $subscription)" wire:navigate>
                                {{ __('Edit') }}
                            </flux:button>
                        </div>
                    </flux:card>
                @endforeach
            </div>

            {{ $subscriptions->links('pagination.flux') }}
        @endif
    </div>
</x-layouts::app>
