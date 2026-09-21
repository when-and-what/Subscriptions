<x-layouts::app :title="__('New Subscription')">
    <div class="flex flex-col gap-6">
        <x-page-header :title="__('New Subscription')" :subtitle="__('Track a subscription against one of your services.')" />

        <flux:card class="max-w-xl">
            <form method="POST" action="{{ route('subscriptions.store') }}" class="flex flex-col gap-6">
                @csrf

                <x-service-select />

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <flux:input type="date" name="start_date" label="{{ __('Start date') }}" value="{{ old('start_date', now()->toDateString()) }}" />
                    <flux:input type="date" name="end_date" label="{{ __('Next renewal (optional)') }}" value="{{ old('end_date') }}" />
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <flux:input type="number" step="0.01" min="0" name="price" label="{{ __('Price (optional)') }}" placeholder="9.99" value="{{ old('price') }}" />

                    <flux:select name="billing_cycle" label="{{ __('Billing cycle') }}">
                        @foreach (\App\Enums\BillingCycle::cases() as $cycle)
                            <option value="{{ $cycle->value }}" @selected(old('billing_cycle') == $cycle->value)>
                                {{ $cycle->label() }}
                            </option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:input name="note" label="{{ __('Note (optional)') }}" value="{{ old('note') }}" placeholder="email@example.com" />

                <input type="hidden" name="auto_renew" value="0">
                <flux:switch name="auto_renew" :checked="(bool) old('auto_renew', true)" label="{{ __('Auto-renews') }}" value="1" />

                <div class="flex items-center gap-2">
                    <flux:button type="submit" variant="primary">{{ __('Create Subscription') }}</flux:button>
                    <flux:button variant="ghost" :href="route('subscriptions.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</x-layouts::app>
