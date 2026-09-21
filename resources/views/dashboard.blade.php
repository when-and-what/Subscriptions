<x-layouts::app :title="__('Dashboard')">
    <div class="flex flex-col gap-6">
        <x-page-header :title="__('Dashboard')" :subtitle="__('Your subscriptions at a glance.')" />

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <flux:card>
                <flux:text>{{ __('Active Subscriptions') }}</flux:text>
                <flux:heading size="2xl">{{ $activeSubscriptionCount }}</flux:heading>
            </flux:card>

            <flux:card>
                <flux:text>{{ __('Expires This Month') }}</flux:text>
                <flux:heading size="2xl">{{ $thisMonth }}</flux:heading>
            </flux:card>

            <flux:card>
                <flux:text>{{ __('Estimated Monthly Cost') }}</flux:text>
                <flux:heading size="2xl">{{ \Illuminate\Support\Number::currency($monthlyTotal) }}</flux:heading>
            </flux:card>
        </div>

        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Upcoming Renewals') }}</flux:heading>

            @if ($upcomingRenewals->isEmpty())
                <flux:text>{{ __('No upcoming renewals.') }}</flux:text>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Service') }}</flux:table.column>
                        <flux:table.column>{{ __('End Date') }}</flux:table.column>
                        <flux:table.column>{{ __('Price') }}</flux:table.column>
                        <flux:table.column>{{ __('Auto-renew') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($upcomingRenewals as $subscription)
                            <flux:table.row>
                                <flux:table.cell>{{ $subscription->service->name }}</flux:table.cell>
                                <flux:table.cell>{{ $subscription->end_date->format('M j, Y') }}</flux:table.cell>
                                <flux:table.cell>
                                    {{ $subscription->price ? \Illuminate\Support\Number::currency($subscription->price) : '—' }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($subscription->auto_renew)
                                        <flux:badge color="teal" size="sm">{{ __('Yes') }}</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">{{ __('No') }}</flux:badge>
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
