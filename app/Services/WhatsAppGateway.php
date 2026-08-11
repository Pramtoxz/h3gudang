<?php

namespace App\Services;

use App\Models\ConfigWA;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppGateway
{
    private ?ConfigWA $config = null;

    public function __construct(private readonly ?int $configId = null)
    {
    }

    public function sendToPhone(string $phoneNumber, string $message): void
    {
        $this->sendText(self::bersihkanNomor($phoneNumber), $message, false);
    }

    public function sendToGroup(string $message, ?string $groupId = null): void
    {
        $target = $groupId ?? $this->config()->wa_group_id;

        if (! $target) {
            throw new RuntimeException('Group ID tidak ditemukan.');
        }

        if (! str_contains($target, '@g.us')) {
            $target .= '@g.us';
        }

        $this->sendText($target, $message, true);
    }

    public function getGroups(): array
    {
        $response = $this->request()->get($this->baseUrl() . '/session/groups', [
            'session' => $this->config()->wa_session_name,
        ]);

        if ($response->failed()) {
            throw new RuntimeException("WhatsApp Gateway error ({$response->status()}): {$response->body()}");
        }

        return $response->json('data', []);
    }

    public static function bersihkanNomor(string $phoneNumber): string
    {
        return preg_replace('/\D+/', '', $phoneNumber);
    }

    private function sendText(string $to, string $message, bool $isGroup): void
    {
        $response = $this->request()->post($this->baseUrl() . '/message/send-text', [
            'session' => $this->config()->wa_session_name,
            'to' => $to,
            'text' => $message,
            'is_group' => $isGroup,
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                "WhatsApp Gateway error ({$response->status()}): " . $response->json('message', $response->body())
            );
        }

        if ($response->json('success') === false) {
            throw new RuntimeException('Gateway error: ' . $response->json('message', 'Unknown error'));
        }
    }

    private function request(): PendingRequest
    {
        return Http::withToken($this->config()->wa_gateway_secret)
            ->acceptJson()
            ->timeout(15)
            ->connectTimeout(5)
            ->withoutVerifying();
    }

    private function baseUrl(): string
    {
        return rtrim($this->config()->wa_gateway_url, '/');
    }

    private function config(): ConfigWA
    {
        if ($this->config !== null) {
            return $this->config;
        }

        $config = $this->configId
            ? ConfigWA::find($this->configId)
            : ConfigWA::first();

        if (! $config) {
            throw new RuntimeException('Konfigurasi WhatsApp tidak ditemukan di database.');
        }

        $kosong = array_keys(array_filter([
            'wa_gateway_url' => empty($config->wa_gateway_url),
            'wa_gateway_secret' => empty($config->wa_gateway_secret),
            'wa_session_name' => empty($config->wa_session_name),
        ]));

        if ($kosong) {
            throw new RuntimeException('Config WhatsApp belum lengkap. Field kosong: ' . implode(', ', $kosong));
        }

        return $this->config = $config;
    }
}
