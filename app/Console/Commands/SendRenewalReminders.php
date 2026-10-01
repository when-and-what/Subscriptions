<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionRenewalReminder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-renewal-reminders')]
#[Description('Email users about auto-renewing subscriptions that renew within their reminder window')]
class SendRenewalReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        User::whereNotNull('renewal_notification_days')
            ->whereNotNull('email_verified_at')
            ->each(function (User $user): void {
                $subscriptions = $user->subscriptions()
                    ->autoRenew()
                    ->whereNull('renewed_subscription_id')
                    ->whereNull('renewal_notified_at')
                    ->whereDate('end_date', '>=', today())
                    ->whereDate('end_date', '<=', today()->addDays($user->renewal_notification_days))
                    ->with('service')
                    ->orderBy('end_date')
                    ->get();

                if ($subscriptions->isEmpty()) {
                    return;
                }

                $user->notify(new SubscriptionRenewalReminder($subscriptions));

                Subscription::whereKey($subscriptions->modelKeys())->update(['renewal_notified_at' => now()]);
            });
    }
}
