<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\Auth\OtpService;
use App\Services\Mail\BrevoMailService;
use App\Services\RecaptchaService; // Importamos el nuevo servicio de reCAPTCHA
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    protected $otpService;
    protected $mailService;
    protected $recaptchaService; // Declaramos la propiedad

    public function __construct(OtpService $otpService, BrevoMailService $mailService, RecaptchaService $recaptchaService)
    {
        $this->otpService = $otpService;
        $this->mailService = $mailService;
        $this->recaptchaService = $recaptchaService; // Inyectamos el servicio
    }

    /**
     * Step 1: Start Registration / Login
     */
    public function start(Request $request)
    {
        Log::info('AuthController::start called', ['email' => $request->email]);

        // Las reglas de validación deben usar snake_case,
        // ya que el middleware CamelCaseToSnakeCaseMiddleware transforma el input antes de la validación.
        $request->validate([
            'email' => 'required|email',
            'first_name'  => 'required|string|max:255', // Ahora en snake_case
            'recaptcha_token' => 'required|string', // Ahora en snake_case
            'action' => 'nullable|string', // La acción de reCAPTCHA, opcional
        ]);

        $recaptchaAction = $request->input('action', 'REGISTER'); // Usamos 'REGISTER' por defecto

        // --- Verificación de reCAPTCHA v3 ---
        $verification = $this->recaptchaService->verifyToken(
            $request->recaptcha_token, // Usamos el campo en snake_case
            $recaptchaAction
        );

        if (!$verification || !$verification['success']) {
            Log::warning('reCAPTCHA v3: Verification failed or token invalid.', [
                'email' => $request->email,
                'verification' => $verification,
            ]);
            return $this->failed('Verificación reCAPTCHA fallida. Inténtalo de nuevo.', 400);
        }

        if (!$this->recaptchaService->isScoreAcceptable($verification['score'])) {
            Log::warning('reCAPTCHA v3: Score too low.', [
                'email' => $request->email,
                'score' => $verification['score'],
                'threshold' => config('services.recaptcha.score_threshold'),
            ]);
            return $this->failed('Acceso denegado por reCAPTCHA. Posible actividad de bot.', 403);
        }
        // --- Fin Verificación de reCAPTCHA v3 ---


        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'first_name' => $request->first_name, // Usamos el campo en snake_case
            ]
        );

        Log::debug('User fetched/created successfully', ['user_id' => $user->id]);

        $otpCode = $this->otpService->generateOtp($user->email);
        $this->mailService->sendOtpEmail($user->email, $otpCode);

        return $this->success(
            [],
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
            return $this->failed('Código inválido o expirado.', 400);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        if (is_null($user->email_verified_at)) {
            $user->email_verified_at = now();
            $user->save();
            Log::debug('User email verified successfully', ['user_id' => $user->id]);
        }

        $token = auth('api')->login($user);

        Log::info('JWT token generated successfully', ['user_id' => $user->id]);

        return $this->success(
            [
                'accessToken' => $token,
                'tokenType' => 'Bearer',
                'expiresIn' => auth('api')->factory()->getTTL() * 60,
            ],
            'Correo verificado exitosamente.'
        );
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Log::info('AuthController::logout called', ['user_id' => auth('api')->id()]);

        auth('api')->logout();

        return $this->success([], 'Sesión cerrada exitosamente.');
    }
}
