<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class CalendarFeedController extends Controller
{
    public function __invoke(Request $request, string $token): Response
    {
        $user = User::query()
            ->where('calendar_feed_token', $token)
            ->firstOrFail();

        $subscriptions = Subscription::query()
            ->whereRelation('service', 'user_id', $user->id)
            ->whereNotNull('end_date')
            ->with('service')
            ->orderBy('end_date')
            ->get();

        $calendar = Calendar::create('Subscription Renewals')->refreshInterval(minutes: 60 * 24);

        foreach ($subscriptions as $subscription) {
            $calendar->event($this->eventFor($subscription, $request));
        }

        return response($calendar->get())
            ->header('Content-Type', 'text/calendar; charset=utf-8');
    }

    private function eventFor(Subscription $subscription, Request $request): Event
    {
        $verb = $subscription->auto_renew ? 'renews' : 'expires';

        $event = Event::create()
            ->name("{$subscription->service->name} {$verb}")
            ->uniqueIdentifier("subscription-{$subscription->id}@{$request->getHost()}")
            ->fullDay()
            ->startsAt($subscription->end_date->toDateTime());

        $descriptionLines = array_filter([
            $subscription->price !== null ? 'Price: $'.number_format($subscription->price, 2) : null,
            'Billing cycle: '.$subscription->billing_cycle->label(),
            $subscription->note,
        ]);

        $event->description(implode("\n", $descriptionLines));

        return $event;
    }
}
