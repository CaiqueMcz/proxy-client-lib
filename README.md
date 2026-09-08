# SSLWS Proxy Client

PHP client for the SSLWS proxy API. Framework-agnostic, works with PHP 7.4+.

## Requirements

- PHP >= 7.4
- [Guzzle](https://github.com/guzzle/guzzle) 7

## Installation

```bash
composer require sslws/proxy-client
```

## Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use SslwsProxy\Client\ProxyClient;

$client = new ProxyClient(
    'https://proxy.example.com',
    'your-api-key'
    // optionally pass Guzzle options as the third argument,
    // e.g. ['verify' => true] to enable SSL verification
);

// Send a webhook request
$response = $client->sendWebhookRequest(
    'https://target.example.com/hook',
    ['payload' => 'value'],
    'optional-x-hash'
);

echo $response->getStatusCode();
echo $response->getBody()->getContents();

// Send a DCV file
$isLive = $client->sendDcvFile('ABCD1234.txt', 'file-content');
var_dump($isLive);
```

## Methods

| Method | Description |
| ------ | ----------- |
| `sendWebhookRequest($url, $params, $xHash = null)` | Sends a webhook via the relay endpoint. |
| `sendDcvFile($fileName, $content, $urlToCheck = null)` | Uploads a DCV file and checks if it is live. |
| `isLive(string $url)` | Checks if a URL returns HTTP 200. |

## Security

SSL verification is disabled by default (`verify => false`) to preserve the
original behaviour. You can enable it by passing `['verify' => true]` as the
third constructor argument.
