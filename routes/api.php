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
Route::get('meta-ads-insights', [MetaAdsInsightsController::class, 'index']);
Route::post('meta-ads-insights', [MetaAdsInsightsController::class, 'store']);
Route::get('meta-ads-insights/{meta_ads_insight}', [MetaAdsInsightsController::class, 'show']);
Route::put('meta-ads-insights/{meta_ads_insight}', [MetaAdsInsightsController::class, 'update']);
Route::patch('meta-ads-insights/{meta_ads_insight}', [MetaAdsInsightsController::class, 'update']);
Route::delete('meta-ads-insights/{meta_ads_insight}', [MetaAdsInsightsController::class, 'destroy']);
