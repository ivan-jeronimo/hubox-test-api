<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\Auth\OtpService;
use App\Services\Mail\BrevoMailService;
use Illuminate\Support\Facades\Log;

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
            'name'  => 'required_without:id|string|max:255'
        ]);

        $user = User::firstOrCreate(
            ['email' => $request->email],
            ['name'  => $request->name ?? 'User']
        );

        Log::debug('User fetched/created successfully', ['user_id' => $user->id]);

        $otpCode = $this->otpService->generateOtp($user->email);
        $this->mailService->sendOtpEmail($user->email, $otpCode);

        return response()->json([
            'message' => 'Código de verificación enviado a tu correo electrónico.'
        ], 200);
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
            return response()->json([
                'error' => 'Código inválido o expirado.'
            ], 400);
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

        return response()->json([
            'message' => 'Correo verificado exitosamente.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user
        ], 200);
    }
}
