<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RetailCrm\Api\Enum\ByIdentifier;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Model\Entity\Orders\Items\OrderProduct;
use RetailCrm\Api\Model\Entity\Orders\Order;
use RetailCrm\Api\Model\Request\BySiteRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersCreateRequest;

class AddOrderController extends Controller
{
    private mixed $retailcrm;

    public function __construct(ContainerInterface $container)
    {
        $this->retailcrm = $container->get('retailcrm');
    }


    public function add(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();

        $client = SimpleClientFactory::createClient($this->retailcrm['url'], $this->retailcrm['apiKey']);

        $request = new OrdersCreateRequest();
        $order = new Order();
        $item = new OrderProduct();

        $item->offer->externalId = ''; //Каталог
        $item->productName = ''; //Имя продукта
        $item->offer->article = ''; //Артикул


        $order->orderType = ''; //Тип
        $order->status = ''; //Статус
        $order->site = ''; //Магазин
        $order->orderMethod = ''; //Способ оформления
        $order->number = ''; //Номер заказа
        $order->lastName = ''; //Фамилия
        $order->firstName = ''; //Имя
        $order->patronymic = ''; //Отчество
        $order->customerComment = ''; //Коментарий
        $order->items = $item;
        $order->company->brand = ''; //Бренд
        $order->customFields = ['prim' => '']; //

        $request->order = $order;

        try {
            $data = $client->orders->create($request);
        } catch (ApiExceptionInterface $e) {
            return $this->responseJsonData(['status' => 'Error', 'Message' => $e->getMessage()], $response);
        }

        $data = $client->orders->get($data->id, new BySiteRequest(ByIdentifier::EXTERNAL_ID, 'retailcrm'));

        return $this->responseJsonData($data, $response);
    }
}