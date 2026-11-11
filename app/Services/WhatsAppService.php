<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Small WhatsApp helper for the Mini CRM.
 *
 * The default "link" driver produces wa.me deep links the user opens in their
 * own WhatsApp. When the Cloud API credentials are configured the "cloud"
 * driver sends the message directly through Meta's Graph API.
 */
class WhatsAppService
{
    public function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return '';
        }

        $code = (string) config('whatsapp.default_country_code', '880');

        // 8801XXXXXXXXX — already international
        if (str_starts_with($digits, $code)) {
            return $digits;
        }

        // 1XXXXXXXXX — missing leading zero (10 digit BD mobile)
        if (str_starts_with($digits, '1') && strlen($digits) === 10) {
            return $code . $digits;
        }

        // 01XXXXXXXXX — local BD number
        if (str_starts_with($digits, '0')) {
            return $code . ltrim($digits, '0');
        }

        // +880 (handled above), 00880… and any other international number
        if (str_starts_with($digits, '00')) {
            return ltrim($digits, '0');
        }

        return $digits;
    }

    public function link(?string $phone, string $message = ''): string
    {
        $number = $this->normalizePhone($phone);

        $url = 'https://wa.me/' . $number;

        if (trim($message) !== '') {
            $url .= '?text=' . rawurlencode($message);
        }

        return $url;
    }

    public function usesCloudApi(): bool
    {
        return config('whatsapp.driver') === 'cloud'
            && filled(config('whatsapp.cloud_token'))
            && filled(config('whatsapp.phone_number_id'));
    }

    /**
     * @return array{ok: bool, driver: string, link: string, response?: array, error?: string}
     */
    public function send(?string $phone, string $message): array
    {
        $link = $this->link($phone, $message);

        if (! $this->usesCloudApi()) {
            return [
                'ok' => true,
                'driver' => 'link',
                'link' => $link,
            ];
        }

        $number = $this->normalizePhone($phone);

        try {
            $response = Http::withToken((string) config('whatsapp.cloud_token'))
                ->acceptJson()
                ->post(sprintf(
                    'https://graph.facebook.com/%s/%s/messages',
                    config('whatsapp.api_version', 'v21.0'),
                    config('whatsapp.phone_number_id')
                ), [
                    'messaging_product' => 'whatsapp',
                    'to' => $number,
                    'type' => 'text',
                    'text' => ['preview_url' => false, 'body' => $message],
                ]);

            if ($response->failed()) {
                Log::warning('WhatsApp Cloud API send failed', [
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);

                return [
                    'ok' => false,
                    'driver' => 'cloud',
                    'link' => $link,
                    'error' => $response->json('error.message') ?? 'WhatsApp API request failed.',
                    'response' => $response->json() ?? [],
                ];
            }

            return [
                'ok' => true,
                'driver' => 'cloud',
                'link' => $link,
                'response' => $response->json() ?? [],
            ];
        } catch (\Throwable $e) {
            Log::warning('WhatsApp Cloud API exception', ['message' => $e->getMessage()]);

            return [
                'ok' => false,
                'driver' => 'cloud',
                'link' => $link,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fill a configured template with lead/institute placeholders.
     */
    public function template(string $key, array $replace = []): string
    {
        $templates = (array) config('whatsapp.templates', []);
        $message = (string) ($templates[$key] ?? ($templates['intro'] ?? ''));

        foreach ($replace as $placeholder => $value) {
            $message = str_replace('{' . $placeholder . '}', (string) $value, $message);
        }

        return $message;
    }
}
