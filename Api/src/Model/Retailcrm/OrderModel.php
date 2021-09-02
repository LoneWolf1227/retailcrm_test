<?php

namespace App\Model\Retailcrm;

use Psr\Container\ContainerInterface;
use RetailCrm\Api\Client;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use RetailCrm\Api\Model\Entity\CustomersCorporate\Company;
use RetailCrm\Api\Model\Entity\Orders\Items\Offer;
use RetailCrm\Api\Model\Entity\Orders\Items\OrderProduct;
use RetailCrm\Api\Model\Entity\Orders\Order;
use RetailCrm\Api\Model\Filter\Orders\OrderFilter;
use RetailCrm\Api\Model\Request\Orders\OrdersCreateRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersEditRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersRequest;
use RetailCrm\Api\Model\Response\Orders\OrdersCreateResponse;
use RetailCrm\Api\Model\Response\Orders\OrdersResponse;

class OrderModel
{
    const STATUSES = [
        'COMPLETED' => 'complete',
        'CANCELLED' => 'prichina-otkaza-v-kliente'
    ];
    /**
     * @var Client|mixed
     */
    private Client $client;

    /**
     * @param OrdersCreateRequest $ordersCreateRequest
     * @param OrdersRequest $ordersRequest
     * @param Order $order
     * @param OrderProduct $item
     * @param ContainerInterface $container
     */
    public function __construct(
        private OrdersCreateRequest $ordersCreateRequest,
        private OrdersRequest       $ordersRequest,
        private Order               $order,
        private OrderProduct        $item,
        ContainerInterface          $container
    )
    {
        $this->client = $container->get(SimpleClientFactory::class);
    }

    /**
     * @param $params
     * @param $product
     * @return array|OrdersCreateResponse
     */
    public function addOrder($params, $product): array|OrdersCreateResponse
    {
        $offer = new Offer();
        $company = new Company();

        $company->brand = $product[0]['manufacturer']; //Бренд
        $company->name = $product[0]['manufacturer'];

        $offer->article = $params['article']; //Артикул

        $this->item->productName = $product[0]['name']; //Имя продукта
        $this->item->offer = $offer;

        $this->order->site = 'test'; //Магазин
        $this->order->orderType = 'fizik'; //Тип
        $this->order->status = 'trouble'; //Статус
        $this->order->orderMethod = 'test'; //Способ оформления
        $this->order->number = $params['code']; //Номер заказа
        $this->order->lastName = $params[0]['attributes']['customer']['lastName']; //Фамилия
        $this->order->firstName = 'АШУРОВтест'; //$params[0]['attributes']['firstName']; //Имя
        $this->order->items = [$this->item];
        $this->order->company = $company;

        $this->ordersCreateRequest->order = $this->order;

        try {
            $data = $this->client->orders->create($this->ordersCreateRequest);
        } catch (ApiExceptionInterface | ClientExceptionInterface $e) {
            return ['status' => 'Error', 'Message' => $e->getMessage()];
        }

        return $data;
    }

    /**
     * @param $number
     * @return OrdersResponse|array
     */
    public function getOrderByNumber($number): OrdersResponse|array
    {
        $this->ordersRequest->filter = new OrderFilter();
        $this->ordersRequest->filter->numbers = is_array($number) ? $number : [(string)$number];
        try {
            $data = $this->client->orders->list($this->ordersRequest);
        } catch (ApiExceptionInterface | ClientExceptionInterface $e) {
            return ['status' => 'Error', 'Message' => $e->getMessage()];
        }

        return $data;
    }

    public function getOrdersByNumber($orders)
    {
        foreach ($orders as $order) {
            $numbers[] = (string)$order['code'];
        }

        return $this->getOrderByNumber($numbers);
    }

    public function updateOrderStatus($externalId, $status): array|OrdersCreateResponse
    {
        $editRequest = new OrdersEditRequest();
        $this->order->externalId = $externalId;
        $this->order->status = $status;

        $editRequest->by = 'externalId';
        $editRequest->order = $this->order;

        try {
            $data = $this->client->orders->edit('externalId', $editRequest);
        } catch (ApiExceptionInterface | ClientExceptionInterface $e) {
            return ['status' => 'Error', 'Message' => $e->getMessage()];
        }

        return $data;
    }

    public function updateOrdersStatuses($retailOrders, $kaspiOldOrders)
    {
        $result = [];
        foreach ($retailOrders as $retailOrder) {
            foreach ($kaspiOldOrders as $oldOrder) {
                if (isset($statuses[$oldOrder['status']]) && $statuses[$oldOrder['status']] !== $retailOrder[0]['status'])
                {
                    $result = $this->updateOrderStatus($retailOrder[0]['externalId'], $statuses[$oldOrder['status']]);
                }
            }
        }

        return $result;
    }

}
