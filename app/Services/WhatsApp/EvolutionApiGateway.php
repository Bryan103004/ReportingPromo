<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsappGatewayInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EvolutionApiGateway implements WhatsappGatewayInterface
{
    protected $baseUrl;
    protected $instance;
    protected $apiKey;

    public function __construct(array $config = [])
    {
        $this->baseUrl = rtrim($config['base_url'] ?? '', '/');
        $this->instance = $config['instance'] ?? null;
        $this->apiKey = $config['api_key'] ?? null;
    }

    public function sendMessage($to, $message, array $options = [])
    {
        if (empty($this->baseUrl) || empty($this->instance) || empty($this->apiKey)) {
            throw new RuntimeException('Evolution API config is incomplete.');
        }

        $url = $this->baseUrl . '/message/sendText/' . $this->instance;

        $payload = array_merge([
            'number' => $to,
            'text' => $message,
        ], $options);

        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if ($response->failed()) {
            throw new RuntimeException('Evolution API request failed: ' . $response->body());
        }

        $raw = $response->json();
        $messageId = null;

        if (is_array($raw)) {
            $messageId = $raw['key']['id'] ?? ($raw['data']['key']['id'] ?? null);
        }

        return [
            'success' => true,
            'provider' => 'evolution',
            'message_id' => $messageId,
            'to' => $to,
            'raw' => $raw,
        ];
    }
}
