<?php

namespace App\Model\Kaspicrm;

use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\ArrayShape;

class OrderModel extends Model
{
    public function getOrderProducts($id)
    {
        $response = $this->client->get('/orderentries/'.$id.'/product');

        return json_decode($response, true);
    }

    public function getOrders(): array|string
    {
        $date = new DateTime('today midnight');
        $mts = $date->getTimestamp().substr($date->format('v'),0,3);
        try {
            $response = $this->client->get($this->host.'/orders', [
                'query' => [
                    'page' => [
                        'number' => 0,
                        'size' => 100
                    ],
                    'filter' => [
                        'orders' => [
                            'state' => 'NEW',
                            'creationDate' => [
                                '$ge' => $mts
                            ],
                        ]
                    ]
                ]
            ]);
        } catch (GuzzleException $e) {
            return ['status' => 'Error', 'message' => $e->getMessage(), 'code' => $e->getCode()];
        }

        return $response->getBody()->getContents();
    }

    #[ArrayShape(['newOrders' => "", 'oldOrders' => "array"])]
    public function getNewAndOldOrders($retailOrders, $kaspiOrders): array
    {
        $oldOrders = [];
        foreach ($kaspiOrders as $key => $kaspiOrder) {
            foreach ($retailOrders as $retailOrder) {
                $retailOrder = (array)$retailOrder;
                if ($kaspiOrder['code'] === $retailOrder[0]['number']) {
                    $oldOrders[] = $kaspiOrders[$key];
                    unset($kaspiOrders[$key]);
                }
            }
        }

        return ['newOrders' => $kaspiOrders, 'oldOrders' => $oldOrders];
    }
}
