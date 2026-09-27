<x-layouts::app :title="$category ? __('Edit Category') : __('New Category')">
    <div class="flex flex-col gap-6">
        <x-page-header :title="$category ? __('Edit Category') : __('New Category')" :subtitle="$category?->name ?? __('Group your services together, like Streaming or Software.')" />

        <flux:card class="max-w-xl">
            <form
                method="POST"
                action="{{ $category ? route('categories.update', $category) : route('categories.store') }}"
                class="flex flex-col gap-6"
            >
                @csrf
                @if ($category)
                    @method('PUT')
                @endif

                <flux:input name="name" label="{{ __('Name') }}" placeholder="Streaming" value="{{ old('name', $category?->name) }}" autofocus />

                <div class="flex items-center gap-2">
                    <flux:button type="submit" variant="primary">
                        {{ $category ? __('Save Changes') : __('Create Category') }}
                    </flux:button>
                    <flux:button variant="ghost" :href="route('categories.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</x-layouts::app>
