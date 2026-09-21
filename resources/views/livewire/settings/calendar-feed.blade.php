<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Calendar feed settings') }}</flux:heading>

    <x-settings.layout :heading="__('Calendar Feed')" :subheading="__('Subscribe to your subscription renewal dates from any calendar app')">
        <div class="my-6 w-full space-y-6">
            <flux:input :value="$this->feedUrl" readonly copyable :label="__('Feed URL')" />

            <flux:text class="text-sm">
                {{ __('Add this URL as a subscribed calendar in Google Calendar, Apple Calendar, or Outlook. Anyone with this link can see your subscription expiration dates, so keep it private.') }}
            </flux:text>

            <div class="flex items-center gap-4">
                <flux:modal.trigger name="confirm-calendar-feed-token-regeneration">
                    <flux:button variant="danger">{{ __('Regenerate URL') }}</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <flux:modal name="confirm-calendar-feed-token-regeneration" focusable class="max-w-lg">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Regenerate calendar feed URL?') }}</flux:heading>

                    <flux:subheading>
                        {{ __('Your current feed URL will stop working immediately. You will need to update the URL in any calendar app where you subscribed to it.') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                    <flux:modal.close>
                        <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>

                    <flux:modal.close>
                        <flux:button variant="danger" wire:click="regenerateToken">{{ __('Regenerate') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </flux:modal>
    </x-settings.layout>
</section>
