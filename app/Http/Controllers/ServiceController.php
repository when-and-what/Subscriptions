<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $services = Service::whereBelongsTo(auth()->user())
            ->with('subscription')
            ->orderBy('name')
            ->paginate(18);

        return view('services.index', [
            'services' => $services,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('services.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceRequest $request): RedirectResponse
    {
        auth()->user()->services()->create($request->validated());

        return redirect()->route('services.index')->with('success', 'Service created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service): View
    {
        Gate::authorize('view', $service);

        $service->load(['subscriptions' => fn ($query) => $query->orderByDesc('start_date')]);

        return view('services.show', [
            'service' => $service,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service): View
    {
        Gate::authorize('update', $service);

        return view('services.edit', ['service' => $service]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        Gate::authorize('update', $service);

        $service->update($request->validated());

        return redirect()->route('services.index')->with('success', 'Service updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service): RedirectResponse
    {
        Gate::authorize('delete', $service);

        $service->subscriptions()->delete();
        $service->delete();

        return redirect()->route('services.index')->with('success', 'Service deleted.');
    }
}
