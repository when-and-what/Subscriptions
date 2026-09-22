<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionRequest;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $subscriptions = Subscription::query()
            ->whereRelation('service', 'user_id', auth()->id())
            ->with('service')
            ->orderBy('end_date')
            ->paginate(12);

        return view('subscriptions.index', ['subscriptions' => $subscriptions]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('subscriptions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubscriptionRequest $request): RedirectResponse
    {
        $subscription = Subscription::create($request->validated());
        $subscription->auto_renew = $request->boolean('auto_renew');
        $subscription->save();

        return redirect()->route('subscriptions.index')->with('success', 'Subscription created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscription $subscription): View
    {
        Gate::authorize('update', $subscription);

        return view('subscriptions.edit', ['subscription' => $subscription]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        Gate::authorize('update', $subscription);

        $subscription->update($request->validated());

        return redirect()->route('subscriptions.index')->with('success', 'Subscription updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription): RedirectResponse
    {
        Gate::authorize('delete', $subscription);

        $subscription->delete();

        return redirect()->route('subscriptions.index')->with('success', 'Subscription deleted.');
    }
}
