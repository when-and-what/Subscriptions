<x-layouts::app :title="__('Edit Subscription')">
    <div class="flex flex-col gap-6">
        <x-page-header :title="__('Edit Subscription')" :subtitle="$subscription->service->name" />

        <flux:card class="max-w-xl">
            <form method="POST" action="{{ route('subscriptions.update', $subscription) }}" class="flex flex-col gap-6">
                @csrf
                @method('PUT')

                @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

                <x-service-select :selected="old('service_id', $subscription->service_id)" />

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <flux:input type="date" name="start_date" label="{{ __('Start date') }}" value="{{ old('start_date', $subscription->start_date?->toDateString()) }}" />
                    <flux:input type="date" name="end_date" label="{{ __('Next renewal (optional)') }}" value="{{ old('end_date', $subscription->end_date?->toDateString()) }}" />
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <flux:input type="number" step="0.01" min="0" name="price" label="{{ __('Price (optional)') }}" value="{{ old('price', $subscription->price) }}" />

                    <flux:select name="billing_cycle" label="{{ __('Billing cycle') }}">
                        @foreach (\App\Enums\BillingCycle::cases() as $cycle)
                            <option value="{{ $cycle->value }}" @selected((int) old('billing_cycle', $subscription->billing_cycle->value) === $cycle->value)>
                                {{ $cycle->label() }}
                            </option>
                        @endforeach
                    </flux:select>
                </div>

                <input type="hidden" name="auto_renew" value="0">
                <flux:switch name="auto_renew" :checked="(bool) old('auto_renew', $subscription->auto_renew)" label="{{ __('Auto-renews') }}" value="1" />

                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <flux:button type="submit" variant="primary">{{ __('Save Changes') }}</flux:button>
                        <flux:button variant="ghost" :href="route('subscriptions.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
                    </div>

                    <flux:modal.trigger name="delete-subscription">
                        <flux:button variant="danger" icon="trash">{{ __('Delete') }}</flux:button>
                    </flux:modal.trigger>
                </div>
            </form>
        </flux:card>
    </div>

    <flux:modal name="delete-subscription" class="min-w-[22rem]">
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Delete this subscription?') }}</flux:heading>
            <flux:text>{{ __('This cannot be undone.') }}</flux:text>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <form method="POST" action="{{ route('subscriptions.destroy', $subscription) }}">
                    @csrf
                    @method('DELETE')
                    <flux:button type="submit" variant="danger">{{ __('Delete Subscription') }}</flux:button>
                </form>
            </div>
        </div>
    </flux:modal>
</x-layouts::app>
