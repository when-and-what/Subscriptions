<?php

namespace App\Http\Controllers;

use App\Http\Requests\Categories\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $categories = Category::whereBelongsTo(Auth::user())
            ->withCount('services')
            ->orderBy('name')
            ->get();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('categories.category', [
            'category' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        Auth::user()->categories()->create($request->validated());

        return redirect()->route('categories.index')->with('success', 'Category created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        Gate::authorize('update', $category);

        return view('categories.category', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        Gate::authorize('update', $category);

        $category->update($request->validated());

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }
}
