<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

Route::get('/ping', function () {
    return response()->json(['message' => 'API is working!']);
});

// Fase 1: Authentication / Onboarding sin contraseña
Route::post('/auth/register-start', [AuthController::class, 'start']);
Route::post('/auth/verify-email-code', [AuthController::class, 'verify']);

// Fase 2 y 3: Rutas protegidas que requieren Token JWT
Route::middleware('auth:api')->group(function () {

    // Aquí irán las rutas del usuario una vez que ya tiene su Token:
    Route::get('/user', function (Request $request) {
        return auth('api')->user();
    });

    // PUT /user/profile
    // POST /user/documents
});
