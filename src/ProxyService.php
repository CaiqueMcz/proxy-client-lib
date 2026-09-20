<?php

namespace SslwsProxy\Client;

use Psr\Http\Message\ResponseInterface;
class ProxyService
{
    private ProxyClient $proxyClient;

    public function __construct(?ProxyClient $proxyClient=null)
    {
        if (is_null($proxyClient) && class_exists(\Illuminate\Foundation\Application::class)) {
            $proxyClient = new ProxyClient(config("services.sslws_proxy.base_url"),
                config("services.sslws_proxy.api_key"));
        }
        $this->proxyClient = $proxyClient;
    }

    public function sendRequestRelay(string $method, string $url, array $params, $xHash = null): ResponseInterface
    {
        return $this->proxyClient->sendRequestRelay($method, $url, $params, $xHash);
    }

    public function sendWebhookRequest($url, $params, $xHash = null): ResponseInterface
    {
        return $this->proxyClient->sendWebhookRequest($url, $params, $xHash);
    }

    public function sendDcvFile($fileName, $content, $urlToCheck = null): bool
    {
        return $this->proxyClient->sendDcvFile($fileName, $content, $urlToCheck);
    }

    public function isLive(string $url): bool
    {
        return $this->proxyClient->isLive($url);
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