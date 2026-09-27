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
        $categoryId = request()->integer('category') ?: null;

        $services = Service::whereBelongsTo(auth()->user())
            ->with(['subscription', 'categories'])
            ->when($categoryId, fn ($query) => $query->whereRelation('categories', 'categories.id', $categoryId))
            ->orderBy('name')
            ->paginate(18)
            ->withQueryString();

        return view('services.index', [
            'services' => $services,
            'categories' => auth()->user()->categories()->orderBy('name')->get(),
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
        $service = auth()->user()->services()->create($request->safe()->except('categories'));

        $service->categories()->sync($request->validated('categories', []));

        return redirect()->route('services.index')->with('success', 'Service created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service): View
    {
        Gate::authorize('view', $service);

        $service->load(['subscriptions' => fn ($query) => $query->orderByDesc('start_date')]);

        $previousUrl = session()->previousUrl();
        $hasPreviousUrl = $previousUrl && $previousUrl !== url()->current();

        return view('services.show', [
            'service' => $service,
            'backUrl' => $hasPreviousUrl ? $previousUrl : route('services.index'),
            'backLabel' => $hasPreviousUrl ? 'Back' : 'Back to Services',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service): View
    {
        Gate::authorize('update', $service);

        $service->load('categories');

        return view('services.edit', ['service' => $service]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        Gate::authorize('update', $service);

        $service->update($request->safe()->except('categories'));

        $service->categories()->sync($request->validated('categories', []));

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
