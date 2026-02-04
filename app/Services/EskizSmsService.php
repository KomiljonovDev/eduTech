<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EskizSmsService
{
    protected string $baseUrl;

    protected string $email;

    protected string $password;

    protected string $from;

    public function __construct()
    {
        $this->baseUrl = config('services.eskiz.base_url');
        $this->email = config('services.eskiz.email');
        $this->password = config('services.eskiz.password');
        $this->from = config('services.eskiz.from');
    }

    /**
     * Send SMS to a phone number.
     */
    public function send(string $phone, string $message, ?string $callbackUrl = null): array
    {
        $phone = $this->normalizePhone($phone);

        $data = [
            'mobile_phone' => $phone,
            'message' => $message,
            'from' => $this->from,
        ];

        if ($callbackUrl) {
            $data['callback_url'] = $callbackUrl;
        }

        $response = $this->client()->asMultipart()->post('/message/sms/send', $data);

        if ($response->failed()) {
            Log::error('Eskiz SMS failed', [
                'phone' => $phone,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('message', 'SMS yuborishda xatolik'),
            ];
        }

        return [
            'success' => true,
            'data' => $response->json(),
        ];
    }

    /**
     * Get user balance and info.
     */
    public function getUser(): array
    {
        $response = $this->client()->get('/auth/user');

        return $response->json();
    }

    /**
     * Get available templates.
     */
    public function getTemplates(): array
    {
        $response = $this->client()->get('/user/templates');

        return $response->json();
    }

    /**
     * Get the authenticated HTTP client.
     */
    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withToken($this->getToken())
            ->acceptJson();
    }

    /**
     * Get or refresh the API token.
     */
    protected function getToken(): string
    {
        return Cache::remember('eskiz_token', now()->addDays(29), function () {
            return $this->login();
        });
    }

    /**
     * Login and get a new token.
     */
    protected function login(): string
    {
        $response = Http::baseUrl($this->baseUrl)
            ->asMultipart()
            ->post('/auth/login', [
                'email' => $this->email,
                'password' => $this->password,
            ]);

        if ($response->failed()) {
            Log::error('Eskiz login failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            throw new \RuntimeException('Eskiz autentifikatsiya xatosi: '.$response->json('message', 'Noma\'lum xato'));
        }

        return $response->json('data.token');
    }

    /**
     * Refresh the token manually.
     */
    public function refreshToken(): string
    {
        $response = $this->client()->patch('/auth/refresh');

        if ($response->successful()) {
            $token = $response->json('data.token');
            Cache::put('eskiz_token', $token, now()->addDays(29));

            return $token;
        }

        // If refresh fails, try full login
        Cache::forget('eskiz_token');

        return $this->getToken();
    }

    /**
     * Normalize phone number to 998XXXXXXXXX format.
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/\D/', '', $phone);

        // Add 998 prefix if missing
        if (strlen($phone) === 9) {
            $phone = '998'.$phone;
        }

        // Remove leading + if exists
        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }
}
