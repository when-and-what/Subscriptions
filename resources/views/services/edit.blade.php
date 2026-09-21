<x-layouts::app :title="__('Edit Service')">
    <div class="flex flex-col gap-6">
        <x-page-header :title="__('Edit Service')" :subtitle="$service->name" />

        <flux:card class="max-w-xl">
            <form method="POST" action="{{ route('services.update', $service) }}" class="flex flex-col gap-6">
                @csrf
                @method('PUT')

                <flux:input name="name" label="{{ __('Name') }}" value="{{ old('name', $service->name) }}" autofocus />

                <flux:input name="url" type="url" label="{{ __('Website (optional)') }}" value="{{ old('url', $service->url) }}" />

                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <flux:button type="submit" variant="primary">{{ __('Save Changes') }}</flux:button>
                        <flux:button variant="ghost" :href="route('services.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
                    </div>

                    <flux:modal.trigger name="delete-service">
                        <flux:button variant="danger" icon="trash">{{ __('Delete') }}</flux:button>
                    </flux:modal.trigger>
                </div>
            </form>
        </flux:card>
    </div>

    <flux:modal name="delete-service" class="min-w-[22rem]">
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Delete :name?', ['name' => $service->name]) }}</flux:heading>
            <flux:text>{{ __('This will also remove any subscriptions tracked under this service. This cannot be undone.') }}</flux:text>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <form method="POST" action="{{ route('services.destroy', $service) }}">
                    @csrf
                    @method('DELETE')
                    <flux:button type="submit" variant="danger">{{ __('Delete Service') }}</flux:button>
                </form>
            </div>
        </div>
    </flux:modal>
</x-layouts::app>
