<?php

use App\Http\Controllers\CalendarFeedController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('calendar/{token}.ics', CalendarFeedController::class)
    ->where('token', '[a-zA-Z0-9]+')
    ->name('calendar.feed');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('services', ServiceController::class);
    Route::resource('subscriptions', SubscriptionController::class)->except(['show']);
});

require __DIR__.'/settings.php';
