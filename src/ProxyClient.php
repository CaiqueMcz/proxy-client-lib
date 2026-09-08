<?php

namespace SslwsProxy\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class ProxyClient
{
    /** @var string */
    private $baseUrl;

    /** @var string */
    private $apiKey;

    /** @var Client */
    private $client;

    public function __construct(string $baseUrl, string $apiKey, array $options = [])
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;

        $this->client = new Client(array_merge([
            'verify' => false,
        ], $options));
    }

    /**
     * @param string            $method
     * @param string            $url
     * @param array             $params
     * @param string|int|null   $xHash
     *
     * @throws GuzzleException
     */
    private function sendRequest($method, $url, array $params, $xHash = null): ResponseInterface
    {
        $headers = [
            'Content-Type' => 'application/json',
            'x-api-key' => $this->apiKey,
        ];

        if ($xHash !== null) {
            $headers['x-hash'] = $xHash;
        }

        return $this->client->request(strtoupper($method), $url, [
            'headers' => $headers,
            'json' => $params,
        ]);
    }

    /**
     * @param string          $method
     * @param string          $url
     * @param array           $params
     * @param string|int|null $xHash
     *
     * @throws GuzzleException
     */
    private function sendRequestRelay(string $method, string $url, array $params, $xHash = null): ResponseInterface
    {
        return $this->sendRequest($method, $this->baseUrl . '/v1/request-relay', [
            'url' => $url,
            'data' => json_encode($params),
            'xHash' => $xHash,
        ]);
    }

    /**
     * @param string          $url
     * @param array           $params
     * @param string|int|null $xHash
     *
     * @throws GuzzleException
     */
    public function sendWebhookRequest($url, $params, $xHash = null): ResponseInterface
    {
        return $this->sendRequestRelay('POST', $url, $params, $xHash);
    }

    /**
     * @param string      $fileName
     * @param string      $content
     * @param string|null $urlToCheck
     *
     * @throws GuzzleException
     */
    public function sendDcvFile($fileName, $content, $urlToCheck = null): bool
    {
        $filename = str_replace('.txt', '', $fileName);

        if ($urlToCheck === null) {
            $urlToCheck = $this->baseUrl . '/.well-known/pki-validation/' . $filename . '.txt';
        }

        $this->sendRequest('post', $this->baseUrl . '/v1/ssl/dcv', [
            'filename' => $filename,
            'content' => $content,
        ]);

        return $this->isLive($urlToCheck);
    }

    public function isLive(string $url): bool
    {
        try {
            $response = $this->client->request('GET', $url);

            return $response->getStatusCode() === 200;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
