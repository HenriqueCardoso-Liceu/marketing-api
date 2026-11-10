<?php

use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\MatriculasController;
use App\Http\Controllers\MetaAdsInsightsController;
use App\Http\Controllers\MetaLeadWebhookController;
use App\Http\Middleware\ApiKeyGuard;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

/*
|--------------------------------------------------------------------------
| Rotas PÚBLICAS (landing pages / formulários / webhook assinado da Meta)
|--------------------------------------------------------------------------
*/
Route::post('/leads', [LeadsController::class, 'store']);

Route::post('/registrations', [RegistrationController::class, 'store']);
// TODO (fase 2): usam ID sequencial; trocar por UUID/token antes de exigir chave
Route::get('/registrations/{registration}', [RegistrationController::class, 'show']);
Route::patch('/registrations/{registration}', [RegistrationController::class, 'update']);

Route::post('/col-itaqua/registrations', [MatriculasController::class, 'storeColItaqua']);
Route::post('/col-doremi/registrations', [MatriculasController::class, 'storeColDoremi']);
Route::post('/col-pequeno-principe/registrations', [MatriculasController::class, 'storeColPequenoPrincipe']);

// Meta receptor (POST valida X-Hub-Signature-256 no controller)
Route::get('/webhooks/meta/leads', [MetaLeadWebhookController::class, 'verify']);
Route::post('/webhooks/meta/leads', [MetaLeadWebhookController::class, 'receive']);

/*
|--------------------------------------------------------------------------
| Rotas INTERNAS — exigem chave (X-Api-Key ou Bearer)
| Fase 1: INTERNAL_API_KEY_ENFORCE=false só registra no log quem chama sem chave.
|--------------------------------------------------------------------------
*/
Route::middleware(ApiKeyGuard::class)->group(function () {
    // Lista TODOS os leads (dados pessoais)
    Route::get('/leads', [LeadsController::class, 'index']);

    Route::get('/meta-ads-insights', [MetaAdsInsightsController::class, 'index']);
    Route::post('/meta-ads-insights', [MetaAdsInsightsController::class, 'store']);
    Route::get('/meta-ads-insights/{meta_ads_insight}', [MetaAdsInsightsController::class, 'show']);
    Route::put('/meta-ads-insights/{meta_ads_insight}', [MetaAdsInsightsController::class, 'update']);
    Route::patch('/meta-ads-insights/{meta_ads_insight}', [MetaAdsInsightsController::class, 'update']);
    Route::delete('/meta-ads-insights/{meta_ads_insight}', [MetaAdsInsightsController::class, 'destroy']);
});
