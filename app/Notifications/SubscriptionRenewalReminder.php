<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class SubscriptionRenewalReminder extends Notification
{
    /**
     * Create a new notification instance.
     *
     * @param  Collection<int, Subscription>  $subscriptions
     */
    public function __construct(public Collection $subscriptions) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(trans_choice('{1} A subscription is renewing soon|[2,*] :count subscriptions are renewing soon', $this->subscriptions->count()))
            ->line(__('The following subscriptions will renew automatically:'));

        foreach ($this->subscriptions as $subscription) {
            $message->line($this->describe($subscription));
        }

        return $message
            ->action(__('View subscriptions'), route('subscriptions.index'))
            ->line(__('To stop receiving these emails, [unsubscribe in your profile settings](:url).', ['url' => route('profile.edit')]));
    }

    /**
     * Describe a subscription's upcoming renewal in a single line.
     */
    protected function describe(Subscription $subscription): string
    {
        $line = __(':service renews :date', [
            'service' => $subscription->service->name,
            'date' => $subscription->end_date->format('M j, Y'),
        ]);

        $cycle = Str::lower($subscription->billing_cycle->label());

        if ($subscription->hasPriceIncreaseAtNextRenewal()) {
            return $line.' — '.__(':price / :cycle (new price)', [
                'price' => Number::currency($subscription->new_price),
                'cycle' => $cycle,
            ]);
        }

        if ($subscription->price) {
            return $line.' — '.Number::currency($subscription->price).' / '.$cycle;
        }

        return $line.' — '.$cycle;
    }
}
