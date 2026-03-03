<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SyncController;

// Esta ruta recibe el webhook de BMG
// BMG hará POST a: https://tu-dominio.com/api/webhook/bmg
Route::post('/webhook/bmg', [SyncController::class, 'handleWebhook']);
