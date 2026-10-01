@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ url('logo.png') }}" class="logo" alt="{{ config('app.name') }}">
<span class="app-name">{{ config('app.name') }}</span>
</a>
</td>
</tr>
