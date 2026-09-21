@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'Laravel')" :logo="asset('logo.png')" alt="{{ config('app.name') }}" {{ $attributes }} />
@else
    <flux:brand :name="config('app.name', 'Laravel')" :logo="asset('logo.png')" alt="{{ config('app.name') }}" {{ $attributes }} />
@endif
