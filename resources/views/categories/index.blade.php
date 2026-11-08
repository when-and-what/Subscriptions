<x-layouts::app :title="__('Categories')">
    <div class="flex flex-col gap-6">
        <x-page-header :title="__('Categories')" :subtitle="__(':count total', ['count' => $categories->count()])">
            <flux:button variant="primary" icon="plus" :href="route('categories.create')" wire:navigate>
                {{ __('New Category') }}
            </flux:button>
        </x-page-header>

        @if ($categories->isEmpty())
            <flux:callout icon="tag" :heading="__('No categories yet')" :text="__('Create categories to group your services, like Streaming or Software.')">
                <x-slot name="actions">
                    <flux:button :href="route('categories.create')" wire:navigate>{{ __('New Category') }}</flux:button>
                </x-slot>
            </flux:callout>
        @else
            <flux:card>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Name') }}</flux:table.column>
                        <flux:table.column>{{ __('Services') }}</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($categories as $category)
                            <flux:table.row>
                                <flux:table.cell>{{ $category->name }}</flux:table.cell>
                                <flux:table.cell>{{ $category->services_count }}</flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button variant="ghost" size="sm" icon="pencil" :href="route('categories.edit', $category)" wire:navigate>
                                            {{ __('Edit') }}
                                        </flux:button>
                                        <flux:modal.trigger name="delete-category-{{ $category->id }}">
                                            <flux:button variant="ghost" size="sm" icon="trash" />
                                        </flux:modal.trigger>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>

                            <flux:modal name="delete-category-{{ $category->id }}" class="min-w-[22rem]">
                                <div class="flex flex-col gap-4">
                                    <flux:heading size="lg">{{ __('Delete :name?', ['name' => $category->name]) }}</flux:heading>
                                    <flux:text>{{ __('This will remove it from any services using it. This cannot be undone.') }}</flux:text>

                                    <div class="flex justify-end gap-2">
                                        <flux:modal.close>
                                            <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                        </flux:modal.close>

                                        <form method="POST" action="{{ route('categories.destroy', $category) }}">
                                            @csrf
                                            @method('DELETE')
                                            <flux:button type="submit" variant="danger">{{ __('Delete Category') }}</flux:button>
                                        </form>
                                    </div>
                                </div>
                            </flux:modal>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        @endif
    </div>
</x-layouts::app>
