<x-layouts::app :title="__('New Service')">
    <div class="flex flex-col gap-6">
        <x-page-header :title="__('New Service')" :subtitle="__('Add a subscription provider to track.')" />

        <flux:card class="max-w-xl">
            <form method="POST" action="{{ route('services.store') }}" class="flex flex-col gap-6">
                @csrf

                <flux:input name="name" label="{{ __('Name') }}" placeholder="Netflix" value="{{ old('name') }}" autofocus />

                <flux:input name="url" type="url" label="{{ __('Website (optional)') }}" placeholder="https://netflix.com" value="{{ old('url') }}" />

                <div class="flex items-center gap-2">
                    <flux:button type="submit" variant="primary">{{ __('Create Service') }}</flux:button>
                    <flux:button variant="ghost" :href="route('services.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</x-layouts::app>
