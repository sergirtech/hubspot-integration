<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\FilialController;

// Webhook de BMG
Route::post('/webhook/bmg', [SyncController::class, 'handleWebhook']);

// CRUD de filiales — forzamos el parámetro a {filial}
Route::apiResource('filiales', FilialController::class)->parameters([
    'filiales' => 'filial'
]);
