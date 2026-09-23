<?php

use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\MatriculasController;
use App\Http\Controllers\MetaAdsInsightsController;
use App\Http\Controllers\MetaLeadWebhookController;

Route::get('/health', function () {
    return dd('API is running');
});

Route::get('/leads', [LeadsController::class, 'index']);
Route::post('/leads', [LeadsController::class, 'store']);

Route::post('/registrations', [RegistrationController::class, 'store']);
Route::get('/registrations/{registration}', [RegistrationController::class, 'show']);
Route::patch('/registrations/{registration}', [RegistrationController::class, 'update']);

// Rotas para interação com a tabela meta_ads_insights
Route::get('/meta-ads-insights', [MetaAdsInsightsController::class, 'index']);
Route::post('/meta-ads-insights', [MetaAdsInsightsController::class, 'store']);
Route::get('/meta-ads-insights/{meta_ads_insight}', [MetaAdsInsightsController::class, 'show']);
Route::put('/meta-ads-insights/{meta_ads_insight}', [MetaAdsInsightsController::class, 'update']);
Route::patch('/meta-ads-insights/{meta_ads_insight}', [MetaAdsInsightsController::class, 'update']);
Route::delete('/meta-ads-insights/{meta_ads_insight}', [MetaAdsInsightsController::class, 'destroy']);


// Rotas para interação com as tabelas de matriculas
Route::post('/col-itaqua/registrations', [MatriculasController::class, 'storeColItaqua']);


// Meta receptor
Route::get('/webhooks/meta/leads', [
    MetaLeadWebhookController::class,
    'verify'
]);

Route::post('/webhooks/meta/leads', [
    MetaLeadWebhookController::class,
    'receive'
]);
