<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\MetaAdsInsightsController;

Route::get('/health', function () {
    return dd('API is running');
});

Route::get('/leads', [LeadsController::class, 'index']);
Route::post('/leads', [LeadsController::class, 'store']);

// Rotas para interação com a tabela meta_ads_insights
Route::apiResource('meta-ads-insights', MetaAdsInsightsController::class);
