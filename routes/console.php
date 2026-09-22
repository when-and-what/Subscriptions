<?php

use App\Console\Commands\RenewSubscription;
use Illuminate\Support\Facades\Schedule;

Schedule::command(RenewSubscription::class)->daily();
