<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FirebaseService
{
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    private const URL_TOKEN = 'https://oauth2.googleapis.com/token';

    private const URL_IID = 'https://iid.googleapis.com/iid/v1:';

    private ?string $projectId = null;

    private ?string $clientEmail = null;

    private ?string $privateKey = null;

    private ?string $accessToken = null;

    private int $tokenExpiresAt = 0;

    public function sendToDevice(string $fcmToken, string $title, string $body, array $data = []): array
    {
        return $this->kirimPesan([
            'token' => $fcmToken,
            'notification' => ['title' => $title, 'body' => $body],
            'data' => $this->normalkanData($data),
        ]);
    }

    public function sendToTopic(string $topic, string $title, string $body, array $data = []): array
    {
        return $this->kirimPesan([
            'topic' => $topic,
            'notification' => ['title' => $title, 'body' => $body],
            'data' => $this->normalkanData($data),
        ]);
    }

    public function sendToMultipleDevices(array $fcmTokens, string $title, string $body, array $data = []): array
    {
        $berhasil = 0;
        $gagal = 0;

        foreach ($fcmTokens as $token) {
            $this->sendToDevice($token, $title, $body, $data)['success'] ? $berhasil++ : $gagal++;
        }

        return ['success' => true, 'successful' => $berhasil, 'failed' => $gagal];
    }

    public function subscribeToTopic(string $fcmToken, string $topic): array
    {
        return $this->kelolaLanggananTopic([$fcmToken], $topic, 'batchAdd');
    }

    public function unsubscribeFromTopic(string $fcmToken, string $topic): array
    {
        return $this->kelolaLanggananTopic([$fcmToken], $topic, 'batchRemove');
    }

    private function kirimPesan(array $message): array
    {
        try {
            $this->muatKredensial();

            $response = Http::withToken($this->ambilAccessToken())
                ->timeout(10)
                ->post(
                    "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send",
                    ['message' => $message]
                );

            if ($response->successful()) {
                Log::info('FCM terkirim', ['name' => $response->json('name', '')]);

                return ['success' => true, 'message' => 'Notification sent successfully'];
            }

            $pesanError = $response->json('error.message', 'Unknown FCM error');
            Log::error('FCM gagal', ['code' => $response->status(), 'error' => $pesanError]);

            return [
                'success' => false,
                'message' => match ($response->status()) {
                    404 => 'FCM token not found or expired',
                    400 => 'Invalid FCM token',
                    default => $pesanError,
                },
                'error_type' => match ($response->status()) {
                    404 => 'token_not_found',
                    400 => 'invalid_token',
                    default => 'general_error',
                },
            ];
        } catch (\Throwable $e) {
            Log::error('FCM Error: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage(), 'error_type' => 'general_error'];
        }
    }

    private function kelolaLanggananTopic(array $tokens, string $topic, string $aksi): array
    {
        try {
            $this->muatKredensial();

            $response = Http::withToken($this->ambilAccessToken())
                ->withHeaders(['access_token_auth' => 'true'])
                ->timeout(10)
                ->post(self::URL_IID . $aksi, [
                    'to' => '/topics/' . $topic,
                    'registration_tokens' => $tokens,
                ]);

            return $response->successful()
                ? ['success' => true, 'message' => 'Topic subscription updated']
                : ['success' => false, 'message' => 'Topic subscription failed'];
        } catch (\Throwable $e) {
            Log::error('FCM Topic Error: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function muatKredensial(): void
    {
        if ($this->projectId !== null) {
            return;
        }

        $path = base_path(config('services.firebase.credentials_path'));

        if (! file_exists($path)) {
            throw new RuntimeException("Firebase credentials file not found: {$path}");
        }

        $kredensial = json_decode(file_get_contents($path), true);

        $this->projectId = $kredensial['project_id'];
        $this->clientEmail = $kredensial['client_email'];
        $this->privateKey = $kredensial['private_key'];
    }

    private function ambilAccessToken(): string
    {
        if ($this->accessToken && time() < $this->tokenExpiresAt - 60) {
            return $this->accessToken;
        }

        $sekarang = time();

        $response = Http::asForm()->timeout(10)->post(self::URL_TOKEN, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $this->buatJwt($sekarang),
        ]);

        $accessToken = $response->json('access_token');

        if (! $accessToken) {
            throw new RuntimeException(
                'Failed to get Firebase access token: ' . $response->json('error_description', $response->body())
            );
        }

        $this->accessToken = $accessToken;
        $this->tokenExpiresAt = $sekarang + (int) $response->json('expires_in', 3600);

        return $this->accessToken;
    }

    private function buatJwt(int $sekarang): string
    {
        $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));

        $payload = $this->base64Url(json_encode([
            'iss' => $this->clientEmail,
            'scope' => self::SCOPE,
            'aud' => self::URL_TOKEN,
            'iat' => $sekarang,
            'exp' => $sekarang + 3600,
        ]));

        $bahanTandaTangan = $header . '.' . $payload;

        openssl_sign($bahanTandaTangan, $tandaTangan, openssl_pkey_get_private($this->privateKey), 'SHA256');

        return $bahanTandaTangan . '.' . $this->base64Url($tandaTangan);
    }

    private function base64Url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function normalkanData(array $data): array
    {
        $data['type'] ??= 'general';
        $data['notification_id'] ??= '0';

        return array_map('strval', $data);
    }
}
