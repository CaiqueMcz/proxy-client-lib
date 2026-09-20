<?php

namespace SslwsProxy\Client;

use Psr\Http\Message\ResponseInterface;
use function SslwsProxy\Client\Laravel\config;

class ProxyService
{
    private ProxyClient $proxyClient;

    public function __construct(?ProxyClient $proxyClient)
    {
        if (is_null($proxyClient) && class_exists(\Illuminate\Foundation\Application::class)) {
            $proxyClient = new ProxyClient(config("services.sslws_proxy.base_url"),
                config("services.sslws_proxy.api_key"));
        }
        $this->proxyClient = $proxyClient;
    }

    public function checkSsl(string $url): ?array
    {
        $response = $this->proxyClient->checkSsl($url);
        if (!$response) {
            return null;
        }
        return $this->parseResponse($response);
    }

    public function getCnpjInfo(?string $cnpj): ?array
    {
        $response = $this->proxyClient->getCnpjInfo($cnpj);
        if (!$response) {
            return null;
        }
        return $this->parseResponse($response);
    }

    public function getZipcodeInfo(?string $zipcode): ?array
    {
        $response = $this->proxyClient->getZipcodeInfo($zipcode);
        if (!$response) {
            return null;
        }
        return $this->parseResponse($response);
    }

    private function parseResponse(ResponseInterface $response): ?array
    {
        return json_decode($response->getBody()->getContents(), true);
    }
}