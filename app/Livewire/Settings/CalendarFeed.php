<?php

namespace App\Livewire\Settings;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Calendar feed settings')]
class CalendarFeed extends Component
{
    #[Computed]
    public function feedUrl(): string
    {
        return route('calendar.feed', ['token' => Auth::user()->calendarFeedToken()]);
    }

    public function regenerateToken(): void
    {
        Auth::user()->rotateCalendarFeedToken();

        unset($this->feedUrl);

        Flux::toast(variant: 'success', text: __('Calendar feed URL regenerated. The previous URL no longer works.'));
    }
}
