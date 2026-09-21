@props([
    'selected' => null,
])

@php
    $services = auth()->user()->services()->orderBy('name')->get();
    $current = old('service_id', $selected);
@endphp

<flux:select name="service_id" label="Service" placeholder="Choose a service...">
    @foreach ($services as $service)
        <option value="{{ $service->id }}" @selected((string) $current === (string) $service->id)>{{ $service->name }}</option>
    @endforeach
</flux:select>
