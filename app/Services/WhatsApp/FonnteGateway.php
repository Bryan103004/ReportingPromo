<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsappGatewayInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FonnteGateway implements WhatsappGatewayInterface
{
    protected $endpoint;
    protected $token;

    public function __construct(array $config = [])
    {
        $this->endpoint = $config['endpoint'] ?? 'https://api.fonnte.com/send';
        $this->token = $config['token'] ?? null;
    }

    public function sendMessage($to, $message, array $options = [])
    {
        if (empty($this->token)) {
            throw new RuntimeException('Fonnte token is not configured.');
        }

        $payload = array_merge([
            'target' => $to,
            'message' => $message,
        ], $options);

        $response = Http::withHeaders([
            'Authorization' => $this->token,
        ])->asForm()->post($this->endpoint, $payload);

        if ($response->failed()) {
            throw new RuntimeException('Fonnte request failed: ' . $response->body());
        }

        $raw = $response->json();
        $messageId = null;

        if (is_array($raw)) {
            $messageId = $raw['id'] ?? ($raw['data']['id'] ?? null);
        }

        return [
            'success' => true,
            'provider' => 'fonnte',
            'message_id' => $messageId,
            'to' => $to,
            'raw' => $raw,
        ];
    }
}
