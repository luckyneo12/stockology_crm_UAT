<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('{module}')->group(function () {

    Route::post('/login',[AuthApiController::class,'login']);
    Route::post('/logout',[AuthApiController::class,'logout'])->middleware('jwt.api.auth');
    Route::post('/refresh',[AuthApiController::class,'refresh'])->middleware('jwt.api.auth');
    Route::post('/edit-profile',[AuthApiController::class,'editProfile'])->middleware('jwt.api.auth');
    Route::post('/change-password',[AuthApiController::class,'changePassword'])->middleware('jwt.api.auth');
    Route::post('/delete-account',[AuthApiController::class,'deleteAccount'])->middleware('jwt.api.auth');
    Route::post('get-workspace-users',[AuthApiController::class,'getWorkspaceUsers'])->middleware('jwt.api.auth');

});

use App\Http\Controllers\ESignController;

Route::get('/esign-templates/{id}/config', [ESignController::class, 'getTemplateConfig']);
Route::post('/esign/callback', [ESignController::class, 'saveSignedDocument']);
Route::get('/ekyc-fetch/{application_id}', [ESignController::class, 'fetchEkycDataDirectly']);



// ── WhatsApp Node.js Callback Routes (no auth middleware — verified by NODE_SECRET) ──
// These endpoints are called by the Node.js whatsapp service, not by the browser.
Route::post('/whatsapp/incoming-webhook', [\Workdo\Lead\Http\Controllers\WhatsAppSessionController::class, 'handleWebhook']);
Route::post('/whatsapp/session-status',   [\Workdo\Lead\Http\Controllers\WhatsAppSessionController::class, 'updateSessionStatus']);
Route::post('/whatsapp/message-ack',      [\Workdo\Lead\Http\Controllers\WhatsAppSessionController::class, 'messageAck']);

