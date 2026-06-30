<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsappGatewayInterface;

class WhatsappService
{
    protected $gateway;

    public function __construct(WhatsappGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function sendText($to, $message, array $options = [])
    {
        return $this->gateway->sendMessage($to, $message, $options);
    }
}
