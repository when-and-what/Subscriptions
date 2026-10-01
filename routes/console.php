<?php

use App\Console\Commands\RenewSubscription;
use App\Console\Commands\SendRenewalReminders;
use Illuminate\Support\Facades\Schedule;

Schedule::command(RenewSubscription::class)->daily();
Schedule::command(SendRenewalReminders::class)->daily();
