<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    protected $secretKey;
    protected $scoreThreshold;

    public function __construct()
    {
        $this->secretKey = config('services.recaptcha.secret_key');
        $this->scoreThreshold = config('services.recaptcha.score_threshold', 0.5); // Default a 0.5
    }

    /**
     * Verifies the reCAPTCHA v3 token with Google.
     *
     * @param string $recaptchaToken The token generated obtained from the client.
     * @param string $expectedAction The name of the action that corresponds to the token.
     * @return array|null Returns an array with assessment details or null on failure.
     */
    public function verifyToken(string $recaptchaToken, string $expectedAction): ?array
    {
        if (!$this->secretKey) {
            Log::error('reCAPTCHA v3: Secret Key is not configured.');
            return null;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $this->secretKey,
                'response' => $recaptchaToken,
            ]);

            $result = $response->json();

            if (!$result['success']) {
                Log::warning('reCAPTCHA v3: Token verification failed.', [
                    'errors' => $result['error-codes'] ?? 'No error codes provided',
                    'recaptchaToken' => $recaptchaToken,
                ]);
                return [
                    'success' => false,
                    'reason' => $result['error-codes'][0] ?? 'UNKNOWN_ERROR',
                    'score' => 0.0,
                    'action' => null,
                    'valid' => false,
                ];
            }

            // Check if the expected action was executed.
            if ($result['action'] !== $expectedAction) {
                Log::warning('reCAPTCHA v3: Action mismatch.', [
                    'expectedAction' => $expectedAction,
                    'receivedAction' => $result['action'],
                ]);
                return [
                    'success' => false,
                    'reason' => 'ACTION_MISMATCH',
                    'score' => $result['score'],
                    'action' => $result['action'],
                    'valid' => true, // Token is valid, but action is wrong
                ];
            }

            Log::info('reCAPTCHA v3: Verification successful.', [
                'score' => $result['score'],
                'action' => $result['action'],
                'valid' => $result['success'],
            ]);

            return [
                'success' => true,
                'score' => $result['score'],
                'action' => $result['action'],
                'valid' => $result['success'],
            ];

        } catch (\Exception $e) {
            Log::error('reCAPTCHA v3: API call failed.', [
                'error' => $e->getMessage(),
                'recaptchaToken' => $recaptchaToken,
            ]);
            return null;
        }
    }

    /**
     * Checks if the reCAPTCHA score is above the configured threshold.
     *
     * @param float $score
     * @return bool
     */
    public function isScoreAcceptable(float $score): bool
    {
        return $score >= $this->scoreThreshold;
    }
}
