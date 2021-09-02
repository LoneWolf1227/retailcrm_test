<?php


namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;

class Controller
{
    protected function responseJsonData($data, Response $response, $code = 400): Response
    {
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (isset($data['status']) && $data['status'] === 'Error') {

            $response = $response->withStatus($code);

            $json = json_encode($data, JSON_PRETTY_PRINT);
            $response->getBody()->write($json);

            return $response->withHeader('Content-Type', 'application/json');
        }

        $json = json_encode($data, JSON_PRETTY_PRINT);
        $response->getBody()->write($json);

        return $response->withHeader('Content-Type', 'application/json');
    }
}