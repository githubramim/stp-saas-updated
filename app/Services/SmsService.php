<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS to a recipient phone number.
     *
     * @param string $phone
     * @param string $message
     * @return array ['success' => bool, 'status' => 'sent'|'failed', 'response' => string]
     */
    public function send(string $phone, string $message): array
    {
        $driver = config('sms.driver', 'log');

        $phone = $this->formatPhoneNumber($phone);

        if ($driver === 'log' || empty($driver)) {
            return $this->sendLog($phone, $message);
        }

        switch ($driver) {
            case 'bulksmsbd':
                return $this->sendBulkSmsBd($phone, $message);
            case 'greenweb':
                return $this->sendGreenweb($phone, $message);
            case 'generic_bd':
            default:
                return $this->sendGenericBd($phone, $message);
        }
    }

    /**
     * Format phone number to standard format.
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^\d]/', '', $phone);
        
        // Convert 017... to 88017... if necessary for standard BD gateways
        if (strlen($cleaned) === 11 && str_starts_with($cleaned, '01')) {
            return '88' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Log driver (for testing/local development).
     */
    protected function sendLog(string $phone, string $message): array
    {
        Log::info("SMS SIMULATION [To: {$phone}]: {$message}");

        return [
            'success' => true,
            'status' => 'sent',
            'response' => json_encode([
                'status' => 'simulated',
                'driver' => 'log',
                'recipient' => $phone,
                'message' => $message,
                'timestamp' => now()->toIso8601String(),
            ]),
        ];
    }

    /**
     * Generic BD SMS Gateway driver.
     */
    protected function sendGenericBd(string $phone, string $message): array
    {
        $config = config('sms.providers.generic_bd');

        if (empty($config['url']) || empty($config['api_key'])) {
            Log::warning("Generic SMS gateway API Key/URL not set. Falling back to log.");
            return $this->sendLog($phone, $message);
        }

        try {
            $response = Http::timeout(10)->post($config['url'], [
                'api_key' => $config['api_key'],
                'sender_id' => $config['sender_id'] ?? '',
                'to' => $phone,
                'message' => $message,
            ]);

            $isSuccess = $response->successful();

            return [
                'success' => $isSuccess,
                'status' => $isSuccess ? 'sent' : 'failed',
                'response' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error("SMS Gateway Error: " . $e->getMessage());

            return [
                'success' => false,
                'status' => 'failed',
                'response' => json_encode(['error' => $e->getMessage()]),
            ];
        }
    }

    /**
     * BulkSMSBD provider driver.
     */
    protected function sendBulkSmsBd(string $phone, string $message): array
    {
        $config = config('sms.providers.bulksmsbd');

        if (empty($config['api_key'])) {
            return $this->sendLog($phone, $message);
        }

        try {
            $response = Http::timeout(10)->get($config['url'], [
                'api_key' => $config['api_key'],
                'type' => 'text',
                'number' => $phone,
                'senderid' => $config['sender_id'] ?? '',
                'message' => $message,
            ]);

            $isSuccess = $response->successful() && str_contains(strtolower($response->body()), 'success');

            return [
                'success' => $isSuccess,
                'status' => $isSuccess ? 'sent' : 'failed',
                'response' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error("BulkSMSBD Error: " . $e->getMessage());
            return [
                'success' => false,
                'status' => 'failed',
                'response' => json_encode(['error' => $e->getMessage()]),
            ];
        }
    }

    /**
     * Greenweb BD provider driver.
     */
    protected function sendGreenweb(string $phone, string $message): array
    {
        $config = config('sms.providers.greenweb');

        if (empty($config['token'])) {
            return $this->sendLog($phone, $message);
        }

        try {
            $response = Http::timeout(10)->post($config['url'], [
                'token' => $config['token'],
                'to' => $phone,
                'message' => $message,
            ]);

            $isSuccess = $response->successful() && !str_contains(strtolower($response->body()), 'error');

            return [
                'success' => $isSuccess,
                'status' => $isSuccess ? 'sent' : 'failed',
                'response' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error("Greenweb SMS Error: " . $e->getMessage());
            return [
                'success' => false,
                'status' => 'failed',
                'response' => json_encode(['error' => $e->getMessage()]),
            ];
        }
    }
}
