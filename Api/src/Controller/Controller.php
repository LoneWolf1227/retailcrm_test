<?php


namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use RetailCrm\Api\Client;
use RetailCrm\Api\Exception\Client\BuilderException;
use RetailCrm\Api\Factory\SimpleClientFactory;

class Controller
{
    protected Client $client;

    /**
     * @throws BuilderException
     */
    public function __construct()
    {
        $this->client = SimpleClientFactory::createClient('https://superposuda.retailcrm.ru/', 'QlnRWTTWw9lv3kjxy1A8byjUmBQedYqb');
    }

    protected function responseJsonData($data, Response $response): Response
    {
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (isset($data['status']) && $data['status'] === 'Error') {

            $response = $response->withStatus(400);

            $json = json_encode($data, JSON_PRETTY_PRINT);
            $response->getBody()->write($json);

            return $response->withHeader('Content-Type', 'application/json');
        }

        $json = json_encode($data, JSON_PRETTY_PRINT);
        $response->getBody()->write($json);

        return $response->withHeader('Content-Type', 'application/json');
    }
}