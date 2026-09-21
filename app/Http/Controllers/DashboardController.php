<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $thisMonth = Subscription::query()
            ->whereRelation('service', 'user_id', $user->id)
            ->where('end_date', '>=', now())
            ->where('end_date', '<=', now()->endOfMonth())
            ->count();

        $activeSubscriptions = Subscription::query()
            ->whereRelation('service', 'user_id', $user->id)
            ->where('end_date', '>=', now());

        $activeSubscriptionCount = $activeSubscriptions->count();

        $monthlyTotal = $activeSubscriptions->get()->sum(
            fn (Subscription $subscription) => $subscription->price
                ? $subscription->price / $subscription->billing_cycle->value
                : 0
        );

        $upcomingRenewals = Subscription::query()
            ->whereRelation('service', 'user_id', $user->id)
            ->where('end_date', '>=', now())
            ->where('end_date', '<=', now()->plus(months: 1))
            ->with('service')
            ->orderBy('end_date', 'asc')
            ->get();

        return view('dashboard', [
            'thisMonth' => $thisMonth,
            'activeSubscriptionCount' => $activeSubscriptionCount,
            'monthlyTotal' => $monthlyTotal,
            'upcomingRenewals' => $upcomingRenewals,
        ]);
    }
}
