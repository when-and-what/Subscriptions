@props([
    'sidebar' => false,
    'name' => config('app.name', 'Laravel'),
])

@if($sidebar)
    <flux:sidebar.brand :name="$name" :logo="asset('logo.png')" alt="{{ config('app.name') }}" {{ $attributes }} />
@else
    <flux:brand :name="$name" :logo="asset('logo.png')" alt="{{ config('app.name') }}" {{ $attributes }} />
@endif
