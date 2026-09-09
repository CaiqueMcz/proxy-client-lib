<?php
require_once "vendor/autoload.php";
$SSLWS_PROXY_KEY = "cGEldU8vN0g1VWAvODZvRmsrfnxjcz5PWjknY0k5Ljg6XVgpeDA9Iyx1dCxsbFU0TDI";
$SSLWS_BASE_URL = "https://api.prxsrv2.gbix.com.br/";
$proxy = new \SslwsProxy\Client\ProxyClient($SSLWS_BASE_URL, $SSLWS_PROXY_KEY);
var_dump($proxy->checkSsl("https://google.com")->getBody()->getContents());