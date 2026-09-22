<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:renew-subscription')]
#[Description('Renew auto-renewing subscriptions that have reached their end date')]
class RenewSubscription extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Subscription::autoRenew()
            ->whereNull('renewed_subscription_id')
            ->whereDate('end_date', '<=', now())
            ->each(function (Subscription $subscription): void {
                $renewal = Subscription::create([
                    'service_id' => $subscription->service_id,
                    'start_date' => $subscription->end_date,
                    'end_date' => $subscription->end_date->copy()->addMonths($subscription->billing_cycle->value),
                    'price' => $subscription->price,
                    'billing_cycle' => $subscription->billing_cycle,
                    'auto_renew' => $subscription->auto_renew,
                    'note' => $subscription->note,
                ]);

                $subscription->renewed_subscription_id = $renewal->id;
                $subscription->save();
            });
    }
}
