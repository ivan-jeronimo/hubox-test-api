<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoMailService
{
    /**
     * Sends the OTP code to the user's email using Brevo (formerly Sendinblue).
     *
     * @param string $email
     * @param string $otpCode
     * @return bool
     */
    public function sendOtpEmail(string $email, string $otpCode): bool
    {
        Log::info('BrevoMailService::sendOtpEmail called', ['email' => $email]);

        $apiKey = config('services.brevo.api_key');

        if (!$apiKey) {
            Log::warning("Brevo API Key is missing. OTP Code for {$email} is {$otpCode}");
            return false;
        }

        $appName = config('app.name', 'App');
        $fromEmail = config('mail.from.address', 'no-reply@myapp.com');

        Log::debug('Sending request to Brevo API', ['from' => $fromEmail, 'to' => $email]);

        // Render the Blade view to HTML string
        $htmlContent = view('emails.otp_verification', ['otpCode' => $otpCode])->render();

        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => ['name' => $appName, 'email' => $fromEmail],
            'to' => [['email' => $email]],
            'subject' => "Tu código de verificación de {$appName}",
            'htmlContent' => $htmlContent
        ]);

        if ($response->successful()) {
            Log::info("BREVO MAIL SENT successfully", ['email' => $email]);
            return true;
        }

        Log::error("BREVO MAIL FAILED", [
            'email' => $email,
            'status' => $response->status(),
            'body' => $response->body()
        ]);
        return false;
    }

    /**
     * Sends an email to the user notifying that their document was approved.
     *
     * @param string $email
     * @param string $userName
     * @param string $documentName
     * @return bool
     */
    public function sendDocumentApprovedEmail(string $email, string $userName, string $documentName): bool
    {
        Log::info('BrevoMailService::sendDocumentApprovedEmail called', ['email' => $email, 'document' => $documentName]);

        $apiKey = config('services.brevo.api_key');
        if (!$apiKey) {
            Log::warning("Brevo API Key is missing. Cannot send approval email to {$email}");
            return false;
        }

        $appName = config('app.name', 'App');
        $fromEmail = config('mail.from.address', 'no-reply@myapp.com');

        $htmlContent = view('emails.document_approved', ['userName' => $userName, 'documentName' => $documentName])->render();

        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => ['name' => $appName, 'email' => $fromEmail],
            'to' => [['email' => $email]],
            'subject' => "¡Documento Aprobado en {$appName}!",
            'htmlContent' => $htmlContent
        ]);

        if ($response->successful()) {
            Log::info("BREVO MAIL SENT successfully (Approval)", ['email' => $email]);
            return true;
        }

        Log::error("BREVO MAIL FAILED (Approval)", [
            'email' => $email,
            'status' => $response->status(),
            'body' => $response->body()
        ]);
        return false;
    }

    /**
     * Sends an email to the user notifying that their document was rejected.
     *
     * @param string $email
     * @param string $userName
     * @param string $documentName
     * @return bool
     */
    public function sendDocumentRejectedEmail(string $email, string $userName, string $documentName): bool
    {
        Log::info('BrevoMailService::sendDocumentRejectedEmail called', ['email' => $email, 'document' => $documentName]);

        $apiKey = config('services.brevo.api_key');
        if (!$apiKey) {
            Log::warning("Brevo API Key is missing. Cannot send rejection email to {$email}");
            return false;
        }

        $appName = config('app.name', 'App');
        $fromEmail = config('mail.from.address', 'no-reply@myapp.com');

        $htmlContent = view('emails.document_rejected', ['userName' => $userName, 'documentName' => $documentName])->render();

        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => ['name' => $appName, 'email' => $fromEmail],
            'to' => [['email' => $email]],
            'subject' => "Atención requerida: Documento Rechazado en {$appName}",
            'htmlContent' => $htmlContent
        ]);

        if ($response->successful()) {
            Log::info("BREVO MAIL SENT successfully (Rejection)", ['email' => $email]);
            return true;
        }

        Log::error("BREVO MAIL FAILED (Rejection)", [
            'email' => $email,
            'status' => $response->status(),
            'body' => $response->body()
        ]);
        return false;
    }
}
