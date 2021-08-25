<?php

namespace App\Controller;

use App\Services\ValidatorService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RetailCrm\Api\Enum\ByIdentifier;
use RetailCrm\Api\Exception\Client\HandlerException;
use RetailCrm\Api\Exception\Client\HttpClientException;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use RetailCrm\Api\Model\Entity\CustomersCorporate\Company;
use RetailCrm\Api\Model\Entity\Orders\Items\Offer;
use RetailCrm\Api\Model\Entity\Orders\Items\OrderProduct;
use RetailCrm\Api\Model\Entity\Orders\Order;
use RetailCrm\Api\Model\Request\BySiteRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersCreateRequest;

class AddOrderController extends Controller
{
    public function __construct(private ValidatorService $validatorService)
    {
    }

    /**
     * @throws \RetailCrm\Api\Exception\Api\ApiErrorException
     * @throws ClientExceptionInterface
     * @throws HandlerException
     * @throws \RetailCrm\Api\Exception\Api\MissingCredentialsException
     * @throws \RetailCrm\Api\Exception\Api\AccountDoesNotExistException
     * @throws ApiExceptionInterface
     * @throws HttpClientException
     * @throws \RetailCrm\Api\Exception\Api\MissingParameterException
     * @throws \RetailCrm\Api\Exception\Client\BuilderException
     * @throws \RetailCrm\Api\Exception\Api\ValidationException
     */
    public function add(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();

        if ($this->validatorService->validateCreateOrderData($params)) {

            $client = SimpleClientFactory::createClient('https://superposuda.retailcrm.ru/', 'QlnRWTTWw9lv3kjxy1A8byjUmBQedYqb');

            $request = new OrdersCreateRequest();
            $order = new Order();
            $offer = new Offer();
            $item = new OrderProduct();
            $company = new Company();

            $company->brand = $params['brand']; //Бренд
            $offer->externalId = $params['externalId']; //Каталог
            $offer->article = $params['article']; //Артикул

            $item->offer = $offer;
            $item->productName = $params['productName']; //Имя продукта

            $order->orderType = $params['orderType']; //Тип
            $order->status = $params['status']; //Статус
            $order->orderMethod = $params['orderMethod']; //Способ оформления
            $order->number = $params['number']; //Номер заказа
            $order->lastName = $params['lastName']; //Фамилия
            $order->firstName = $params['firstName']; //Имя
            $order->patronymic = $params['patronymic']; //Отчество
            $order->customerComment = $params['customerComment']; //Коментарий
            $order->customFields = ['prim' => $params['prim']]; //Пользовательское поле примечание (prim)
            $order->items = $item;
            $order->company = $company;

            $request->order = $order;
            $request->site = $params['site']; //Магазин

            try {
                $data = $client->orders->create($request);
            } catch (ApiExceptionInterface | ClientExceptionInterface $e) {
                return $this->responseJsonData(['status' => 'Error', 'Message' => $e->getMessage()], $response);
            }

            $data = $client->orders->get($data->id, new BySiteRequest(ByIdentifier::EXTERNAL_ID, 'retailcrm'));

            return $this->responseJsonData($data, $response);
        }

        return $this->responseJsonData(['status' => 'Error'], $response);
    }
}