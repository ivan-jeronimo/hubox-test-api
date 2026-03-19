<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\Auth\OtpService;
use App\Services\Mail\BrevoMailService; // Corregido: Usar '\' en lugar de '->'
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource; // Importamos el UserResource

class AuthController extends Controller
{
    protected $otpService;
    protected $mailService;

    public function __construct(OtpService $otpService, BrevoMailService $mailService)
    {
        $this->otpService = $otpService;
        $this->mailService = $mailService;
    }

    /**
     * Step 1: Start Registration / Login
     */
    public function start(Request $request)
    {
        Log::info('AuthController::start called', ['email' => $request->email]);

        $request->validate([
            'email' => 'required|email',
            'first_name'  => 'required|string|max:255', // Solo el primer nombre es requerido aquí
            // 'paternal_surname' ya NO es requerido en este paso
        ]);

        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'first_name' => $request->first_name,
                // Los apellidos y middle_name son nullable en la BD, no es necesario pasarlos aquí
            ]
        );

        Log::debug('User fetched/created successfully', ['user_id' => $user->id]);

        $otpCode = $this->otpService->generateOtp($user->email);
        $this->mailService->sendOtpEmail($user->email, $otpCode);

        // Respuesta estandarizada con el método success del Trait
        return $this->success(
            [], // No necesitamos devolver datos específicos en este punto
            'Código de verificación enviado a tu correo electrónico.'
        );
    }

    /**
     * Step 2: Verify OTP and Generate JWT
     */
    public function verify(Request $request)
    {
        Log::info('AuthController::verify called', ['email' => $request->email]);

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code'  => 'required|string|size:6'
        ]);

        $isValid = $this->otpService->validateOtp($request->email, $request->code);

        if (!$isValid) {
            Log::warning('OTP validation failed', ['email' => $request->email, 'code' => $request->code]);
            // Respuesta estandarizada con el método failed del Trait
            return $this->failed('Código inválido o expirado.', 400);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Mark email as verified if it wasn't already
        if (is_null($user->email_verified_at)) {
            $user->email_verified_at = now();
            $user->save();
            Log::debug('User email verified successfully', ['user_id' => $user->id]);
        }

        // Generate JWT Token using Tymon/jwt-auth
        $token = auth('api')->login($user);

        Log::info('JWT token generated successfully', ['user_id' => $user->id]);

        // Respuesta estandarizada con el método success del Trait, con claves en camelCase
        return $this->success(
            [
                'accessToken' => $token, // camelCase
                'tokenType' => 'Bearer', // camelCase
                'expiresIn' => auth('api')->factory()->getTTL() * 60, // camelCase
            ],
            'Correo verificado exitosamente.'
        );
    }
}
