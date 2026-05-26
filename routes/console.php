<?php

use App\Jobs\SyncMetaAdsInsightsJob;
use Illuminate\Support\Facades\Schedule;

$accounts = config('services.meta.accounts');

// Diário — últimos 2 dias
Schedule::call(function () use ($accounts) {
    foreach ($accounts as $account) {
        SyncMetaAdsInsightsJob::dispatch(
            account: $account,
            since: now()->subDays(2)->toDateString(),
            until: now()->subDay()->toDateString(),
        );
    }
})->dailyAt('06:00');

// Semanal — reprocessa janela de atribuição (28 dias)
Schedule::call(function () use ($accounts) {
    foreach ($accounts as $account) {
        SyncMetaAdsInsightsJob::dispatch(
            account: $account,
            since: now()->subDays(28)->toDateString(),
            until: now()->subDay()->toDateString(),
        );
    }
})->weeklyOn(1, '07:00');
