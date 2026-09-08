<?php

namespace SslwsProxy\Client\Tests;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SslwsProxy\Client\ProxyClient;

class ProxyClientTest extends TestCase
{
    private const BASE_URL = 'https://proxy.example.com';
    private const API_KEY = 'test-api-key';

    private function makeClient(array $responses, string $baseUrl = self::BASE_URL, array &$container = []): ProxyClient
    {
        $container = [];
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        $handler->push(Middleware::history($container));

        return new ProxyClient($baseUrl, self::API_KEY, ['handler' => $handler]);
    }

    public function testSendWebhookRequestSendsRelayPost(): void
    {
        $container = [];
        $client = $this->makeClient([new Response(200)], self::BASE_URL, $container);

        $response = $client->sendWebhookRequest('https://target.example.com/hook', ['foo' => 'bar'], 'hash123');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(1, $container);

        $request = $container[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(self::BASE_URL . '/v1/request-relay', (string) $request->getUri());
        $this->assertSame(self::API_KEY, $request->getHeaderLine('x-api-key'));
        $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));

        $body = json_decode((string) $request->getBody(), true);
        $this->assertSame('https://target.example.com/hook', $body['url']);
        $this->assertSame('{"foo":"bar"}', $body['data']);
        $this->assertSame('hash123', $body['xHash']);
    }

    public function testSendWebhookRequestWithoutHash(): void
    {
        $container = [];
        $client = $this->makeClient([new Response(200)], self::BASE_URL, $container);

        $client->sendWebhookRequest('https://target.example.com/hook', []);

        $request = $container[0]['request'];
        $this->assertFalse($request->hasHeader('x-hash'));
    }

    public function testBaseUrlTrailingSlashIsTrimmed(): void
    {
        $container = [];
        $client = $this->makeClient([new Response(200)], 'https://proxy.example.com/', $container);

        $client->sendWebhookRequest('https://target.example.com/hook', []);

        $this->assertSame(
            'https://proxy.example.com/v1/request-relay',
            (string) $container[0]['request']->getUri()
        );
    }

    public function testSendDcvFile(): void
    {
        $container = [];
        $client = $this->makeClient([
            new Response(200),
            new Response(200),
        ], self::BASE_URL, $container);

        $result = $client->sendDcvFile('ABCD1234.txt', 'file-content');

        $this->assertTrue($result);
        $this->assertCount(2, $container);

        $dcvRequest = $container[0]['request'];
        $this->assertSame('POST', $dcvRequest->getMethod());
        $this->assertSame(self::BASE_URL . '/v1/ssl/dcv', (string) $dcvRequest->getUri());

        $body = json_decode((string) $dcvRequest->getBody(), true);
        $this->assertSame('ABCD1234', $body['filename']);
        $this->assertSame('file-content', $body['content']);

        $liveRequest = $container[1]['request'];
        $this->assertSame('GET', $liveRequest->getMethod());
        $this->assertSame(
            self::BASE_URL . '/.well-known/pki-validation/ABCD1234.txt',
            (string) $liveRequest->getUri()
        );
    }

    public function testSendDcvFileWithCustomUrlToCheck(): void
    {
        $container = [];
        $client = $this->makeClient([
            new Response(200),
            new Response(200),
        ], self::BASE_URL, $container);

        $result = $client->sendDcvFile('ABCD1234.txt', 'content', 'https://custom.example.com/check.txt');

        $this->assertTrue($result);
        $this->assertSame(
            'https://custom.example.com/check.txt',
            (string) $container[1]['request']->getUri()
        );
    }

    public function testIsLiveReturnsTrueOn200(): void
    {
        $client = $this->makeClient([new Response(200)]);

        $this->assertTrue($client->isLive('https://example.com/ok'));
    }

    public function testIsLiveReturnsFalseOn404(): void
    {
        $client = $this->makeClient([new Response(404)]);

        $this->assertFalse($client->isLive('https://example.com/missing'));
    }

    public function testIsLiveReturnsFalseOnException(): void
    {
        $client = $this->makeClient([
            new RequestException('Connection error', new Request('GET', 'https://example.com/error')),
        ]);

        $this->assertFalse($client->isLive('https://example.com/error'));
    }
}
