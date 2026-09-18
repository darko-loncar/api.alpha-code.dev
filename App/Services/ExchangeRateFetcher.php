<?php
declare(strict_types=1);
namespace App\Services;
use Components\HttpClient;

class ExchangeRateFetcher
{
    private HttpClient $http;
    private string $apiUrl;

    public function __construct(HttpClient $http, string $apiUrl) {
        $this->apiUrl = $apiUrl;
        $this->http = $http;
    }

    public function fetch() {
        return $this->http->get($this->apiUrl);

    }
}
