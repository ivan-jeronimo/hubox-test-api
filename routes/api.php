<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\UserDocumentController;
use App\Http\Resources\UserResource;

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

    // Rutas de usuario
    Route::get('/user', function (Request $request) {
        // Usamos UserResource para transformar los datos del usuario autenticado
        return new UserResource($request->user());
    });
    Route::put('/user/profile', [UserController::class, 'updateProfile']);

    // Rutas de tipos de documentos
    Route::get('/document-types', [DocumentTypeController::class, 'index']);

    // Rutas para documentos del usuario
    Route::post('/user/documents', [UserDocumentController::class, 'store']);
    Route::get('/user/documents', [UserDocumentController::class, 'index']); // Nueva ruta para listar documentos
});
