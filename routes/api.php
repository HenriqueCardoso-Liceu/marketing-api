<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadsController;

Route::get('/health', function () {
    return dd('API is running');
});

Route::get('/leads', [LeadsController::class, 'index']);
Route::post('/leads', [LeadsController::class, 'store']);
