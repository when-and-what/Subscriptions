<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', DashboardController::class)->name('home');
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('services', ServiceController::class);
    Route::resource('subscriptions', SubscriptionController::class)->except(['show']);
});

require __DIR__.'/settings.php';
