<?php

namespace SslwsProxy\Client\Tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SslwsProxy\Client\ProxyClient;
use SslwsProxy\Client\ProxyService;

class ProxyServiceTest extends TestCase
{
    private function makeService(ProxyClient $client): ProxyService
    {
        return new ProxyService($client);
    }

    public function testSendRequestRelayDelegatesToClient(): void
    {
        $response = new Response(200);
        $client = $this->createMock(ProxyClient::class);
        $client->expects($this->once())
            ->method('sendRequestRelay')
            ->with('POST', 'https://target.example.com/hook', ['foo' => 'bar'], 'hash123')
            ->willReturn($response);

        $result = $this->makeService($client)
            ->sendRequestRelay('POST', 'https://target.example.com/hook', ['foo' => 'bar'], 'hash123');

        $this->assertSame($response, $result);
    }

    public function testSendWebhookRequestDelegatesToClient(): void
    {
        $response = new Response(200);
        $client = $this->createMock(ProxyClient::class);
        $client->expects($this->once())
            ->method('sendWebhookRequest')
            ->with('https://target.example.com/hook', ['foo' => 'bar'], 'hash123')
            ->willReturn($response);

        $result = $this->makeService($client)
            ->sendWebhookRequest('https://target.example.com/hook', ['foo' => 'bar'], 'hash123');

        $this->assertSame($response, $result);
    }

    public function testSendDcvFileDelegatesToClient(): void
    {
        $client = $this->createMock(ProxyClient::class);
        $client->expects($this->once())
            ->method('sendDcvFile')
            ->with('ABCD1234.txt', 'content')
            ->willReturn(true);

        $this->assertTrue($this->makeService($client)->sendDcvFile('ABCD1234.txt', 'content'));
    }

    public function testIsLiveDelegatesToClient(): void
    {
        $client = $this->createMock(ProxyClient::class);
        $client->expects($this->once())
            ->method('isLive')
            ->with('https://example.com')
            ->willReturn(true);

        $this->assertTrue($this->makeService($client)->isLive('https://example.com'));
    }

    public function testCheckSslReturnsParsedArray(): void
    {
        $client = $this->createMock(ProxyClient::class);
        $client->expects($this->once())
            ->method('checkSsl')
            ->with('https://example.com')
            ->willReturn(new Response(200, [], '{"valid":true}'));

        $result = $this->makeService($client)->checkSsl('https://example.com');

        $this->assertSame(['valid' => true], $result);
    }

    public function testCheckSslReturnsNullWhenClientReturnsNull(): void
    {
        $client = $this->createMock(ProxyClient::class);
        $client->expects($this->once())
            ->method('checkSsl')
            ->with('https://example.com')
            ->willReturn(null);

        $result = $this->makeService($client)->checkSsl('https://example.com');

        $this->assertNull($result);
    }

    public function testGetCnpjInfoReturnsParsedArray(): void
    {
        $client = $this->createMock(ProxyClient::class);
        $client->expects($this->once())
            ->method('getCnpjInfo')
            ->with('12345678000199')
            ->willReturn(new Response(200, [], '{"cnpj":"12345678000199"}'));

        $result = $this->makeService($client)->getCnpjInfo('12345678000199');

        $this->assertSame(['cnpj' => '12345678000199'], $result);
    }

    public function testGetCnpjInfoReturnsNullWhenClientReturnsNull(): void
    {
        $client = $this->createMock(ProxyClient::class);
        $client->expects($this->once())
            ->method('getCnpjInfo')
            ->with('12345678000199')
            ->willReturn(null);

        $result = $this->makeService($client)->getCnpjInfo('12345678000199');

        $this->assertNull($result);
    }

    public function testGetZipcodeInfoReturnsParsedArray(): void
    {
        $client = $this->createMock(ProxyClient::class);
        $client->expects($this->once())
            ->method('getZipcodeInfo')
            ->with('01001000')
            ->willReturn(new Response(200, [], '{"zipcode":"01001000"}'));

        $result = $this->makeService($client)->getZipcodeInfo('01001000');

        $this->assertSame(['zipcode' => '01001000'], $result);
    }

    public function testGetZipcodeInfoReturnsNullWhenClientReturnsNull(): void
    {
        $client = $this->createMock(ProxyClient::class);
        $client->expects($this->once())
            ->method('getZipcodeInfo')
            ->with('01001000')
            ->willReturn(null);

        $result = $this->makeService($client)->getZipcodeInfo('01001000');

        $this->assertNull($result);
    }
}
