<?php

use App\Http\Controllers\Api\FonnteWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/fonnte-webhook', [FonnteWebhookController::class, 'handle']);