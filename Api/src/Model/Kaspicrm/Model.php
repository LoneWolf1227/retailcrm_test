<?php

namespace App\Model\Kaspicrm;

use GuzzleHttp\Client;

class Model
{
    protected Client $client;
    protected string $host;

    public function __construct()
    {
        $this->host = 'https://kaspi.kz/shop/api/v2';
        $this->client = new Client([
            'headers' => [
                'Content-Type' => 'application/vnd.api+json',
                'X-Auth-Token' => '{kaspiApiToken}'
            ]
        ]);
    }
}