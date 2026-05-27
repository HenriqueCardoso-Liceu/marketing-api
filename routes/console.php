<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('meta:sync-insights --days=2')->dailyAt('06:00');
Schedule::command('meta:sync-insights --days=28')->weeklyOn(1, '07:00');
