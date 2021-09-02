<?php

namespace App\Controller;

use App\Model\Retailcrm\OrderModel as RetailOrderModel;
use App\Model\Kaspicrm\OrderModel as KaspiOrderModel;
use Psr\Http\Message\ResponseInterface as Response;

class OrderController extends Controller
{
    /**
     * @param Response $response
     * @param RetailOrderModel $retailOrderModel
     * @param KaspiOrderModel $kaspiOrderModel
     * @return Response
     */
    public function addAndUpdateStatuses(Response $response, RetailOrderModel $retailOrderModel, KaspiOrderModel $kaspiOrderModel): Response
    {
        $kaspiOrders = $kaspiOrderModel->getOrders();
        if (isset($kaspiOrders['status']) && $kaspiOrders['status'] === 'Error') {
            return $this->responseJsonData($kaspiOrders, $response, $kaspiOrders['code']);
        }

        $retailOrders = $retailOrderModel->getOrdersByNumber($kaspiOrders);

        $orders = $kaspiOrderModel->getNewAndOldOrders($retailOrders, $kaspiOrders);

        $retailOrderModel->updateOrdersStatuses($retailOrders, $orders['oldOrders']);

        if (!empty($orders['newOrders'])) {
            foreach ($orders['newOrders'] as $newOrder) {
                $product = $kaspiOrderModel->getOrderProducts($newOrder[0]['id']);
                $data = $retailOrderModel->addOrder($newOrder, $product);
            }
            return $this->responseJsonData(['message' => 'New Orders added', 'data' => $data], $response);
        }

        return $this->responseJsonData(['message' => 'Nothing to add', 'data' => $kaspiOrders], $response);
    }
}