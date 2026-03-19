<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Generates a 6-digit OTP code and stores it in Cache for 15 minutes.
     *
     * @param string $email
     * @return string
     */
    public function generateOtp(string $email): string
    {
        Log::info('OtpService::generateOtp called', ['email' => $email]);

        // Generate a random 6-digit number
        $otp = (string) random_int(100000, 999999);

        // Store in cache with the email as key, valid for 15 minutes
        Cache::put('otp_' . $email, $otp, now()->addMinutes(15));

        Log::debug('OTP code generated and cached', ['email' => $email]);

        return $otp;
    }

    /**
     * Validates if the provided OTP matches the one in cache.
     *
     * @param string $email
     * @param string $otp
     * @return bool
     */
    public function validateOtp(string $email, string $otp): bool
    {
        Log::info('OtpService::validateOtp called', ['email' => $email]);

        $cachedOtp = Cache::get('otp_' . $email);

        if ($cachedOtp && $cachedOtp === $otp) {
            // Once validated, remove it so it cannot be reused
            Cache::forget('otp_' . $email);
            Log::debug('OTP validated successfully', ['email' => $email]);
            return true;
        }

        Log::warning('OTP validation failed or expired in cache', ['email' => $email]);
        return false;
    }
}
