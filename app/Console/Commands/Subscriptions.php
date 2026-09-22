<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\Subscription;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\number;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

#[Signature('app:subscriptions')]
#[Description('Command description')]
class Subscriptions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $option = 'Subscriptions';
        while ($option != 'Exit') {
            $option = select(
                label: 'What would you like to manage?',
                options: ['Services', 'Subscriptions', 'Exit'],
                default: $option,
            );
            if ($option == 'Services') {
                $this->manageServices();
            } elseif ($option == 'Subscriptions') {
                $this->manageSubscriptions();
            }
        }
    }

    private function manageServices()
    {
        $option = select(
            label: 'What would you like todo?',
            options: ['Add', 'List', 'Delete'],
        );
        if ($option == 'Add') {
            $this->createService();
        } elseif ($option == 'List') {
            table(
                headers: ['ID', 'Name', 'URL'],
                rows: Service::all('id', 'name', 'url')->toArray(),
            );
        } elseif ($option == 'Delete') {
            $service_id = number(
                label: 'What\'s the service id?',
                required: true,
            );
            Service::destroy($service_id);
        }
    }

    private function manageSubscriptions()
    {
        $option = select(
            label: 'What would you like todo?',
            options: ['Add', 'List Active', 'Update'],
        );
        if ($option == 'Add') {
            $this->createSubscription();
        } elseif ($option == 'List Active') {
            table(
                headers: ['Name', 'Start Date', 'End Date', 'Price'],
                rows: Subscription::with('service:id,name')
                    ->get(['id', 'service_id', 'start_date', 'end_date', 'price'])
                    ->map(fn (Subscription $subscription) => [
                        $subscription->service->name,
                        $subscription->start_date->format('n/j/y'),
                        $subscription->end_date->format('n/j/y'),
                        $subscription->price ? '$'.number_format($subscription->price, 2) : '—',
                    ])
                    ->toArray(),
            );
        } elseif ($option == 'Update') {
            $subscriptions = Subscription::with('service')->orderBy('end_date', 'asc')->get()->pluck('service.name', 'id')->toArray();
            $subscription_id = select(
                label: 'What service is this for?',
                options: $subscriptions,
            );
            $subscription = Subscription::find($subscription_id);
            [$start_date, $end_date, $price, $billing_cycle] = $this->promptSubscription($subscription);
            $subscription->fill([
                'start_date' => $start_date,
                'end_date' => $end_date,
                'price' => $price,
                'billing_cycle' => $billing_cycle,
            ]);
            $subscription->save();
        }
    }

    private function createSubscription()
    {
        $services = Service::orderBy('name', 'asc')->pluck('name', 'id')->toArray();
        $service_id = select(
            label: 'What service is this for?',
            options: $services,
        );
        [$start, $price, $cycle] = $this->promptSubscription(null);
        $subscription = new Subscription;
        $subscription->service_id = $service_id;
        $subscription->fill([
            'start_date' => $start,
            'end_date' => null,
            'price' => $price,
            'billing_cycle' => $cycle,
        ]);
        $subscription->save();
    }

    private function promptSubscription(?Subscription $subscription): array
    {
        $start = text(
            label: 'What date did this start?',
            required: true,
            placeholder: 'mm/dd/YYYY',
            validate: ['start' => 'date'],
            default: $subscription?->start_date?->toDateString() ?? '',
        );
        $end = text(
            label: 'What does this end?',
            required: false,
            placeholder: 'mm/dd/YYYY',
            validate: ['start' => 'date'],
            default: $subscription?->end_date?->toDateString() ?? '',
        );
        $price = text(
            label: 'How much does it cost?',
            required: false,
            placeholder: '9.99',
            validate: ['price' => 'decimal:0,2'],
            default: $subscription?->price,
        );
        $cycle = number(
            label: 'How many months per billy cycle?',
            required: true,
            default: $subscription->billing_cycle ?? 1,
        );

        return [$start, $end, $price, $cycle];
    }

    private function createService()
    {
        $name = text(
            label: 'What is the name of the service?',
            required: true
        );
        $url = text(
            label: 'What is the URL of the service?',
            required: false,
            placeholder: 'https://example.com/manage',
        );
        $user_id = number(
            label: 'What\'s your user ID?',
            required: true,
            default: 1,
        );
        $service = new Service;
        $service->name = $name;
        $service->url = $url;
        $service->user_id = $user_id;
        $service->save();
        table(
            headers: ['ID', 'Name', 'URL'],
            rows: [[$service->id, $name, $url]],
        );
    }
}
