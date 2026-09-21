<flux:footer container class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <footer class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div>
            <flux:heading>{{ config('app.name') }}</flux:heading>
            <flux:text class="mt-1">Track every subscription in one place.</flux:text>
        </div>

        <nav class="flex flex-col gap-2 md:items-center">
            <flux:link :href="route('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:link>
            <flux:link :href="route('services.index')" wire:navigate>{{ __('Services') }}</flux:link>
            <flux:link :href="route('subscriptions.index')" wire:navigate>{{ __('Subscriptions') }}</flux:link>
        </nav>

        <div class="md:text-right">
            <flux:text>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</flux:text>
        </div>
    </footer>
</flux:footer>
