<?php

namespace App\Controller;

use App\Services\ValidatorService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use RetailCrm\Api\Model\Entity\CustomersCorporate\Company;
use RetailCrm\Api\Model\Entity\Orders\Items\Offer;
use RetailCrm\Api\Model\Entity\Orders\Items\OrderProduct;
use RetailCrm\Api\Model\Entity\Orders\Order;
use RetailCrm\Api\Model\Request\Orders\OrdersCreateRequest;

class AddOrderController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @param ValidatorService $validatorService
     * @return Response
     */
    public function add(Request $request, Response $response, ValidatorService $validatorService): Response
    {
        $params = $request->getParsedBody();

        if ($validatorService->validateCreateOrderData($params)) {

            $request = new OrdersCreateRequest();
            $order = new Order();
            $offer = new Offer();
            $item = new OrderProduct();
            $company = new Company();

            $company->brand = $params['brand']; //Бренд
            $offer->externalId = $params['externalId']; //Каталог
            $offer->article = $params['article']; //Артикул
            $item->productName = $params['productName']; //Имя продукта

            $order->site = $params['site']; //Магазин
            $order->orderType = $params['orderType']; //Тип
            $order->status = $params['status']; //Статус
            $order->orderMethod = $params['orderMethod']; //Способ оформления
            $order->number = $params['number']; //Номер заказа
            $order->lastName = $params['lastName']; //Фамилия
            $order->firstName = $params['firstName']; //Имя
            $order->patronymic = $params['patronymic']; //Отчество
            $order->customerComment = $params['customerComment']; //Коментарий
            $order->customFields = ['prim' => $params['prim']]; //Пользовательское поле примечание (prim)
            $order->items = [$item];
            $order->company = $company;

            $request->order = $order;

            try {
                $data = $this->client->orders->create($request);
            } catch (ApiExceptionInterface | ClientExceptionInterface $e) {
                return $this->responseJsonData(['status' => 'Error', 'Message' => $e->getMessage(), 'trace' => $e->getTrace()], $response);
            }

            return $this->responseJsonData($data, $response);
        }

        return $this->responseJsonData(['status' => 'Error'], $response);
    }
}