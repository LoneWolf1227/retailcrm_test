<?php
declare(strict_types=1);

use App\Controller\OrderController;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->group('/orders', function (Group $group) {
        $group->post('/add', [OrderController::class, 'addAndUpdateStatuses']);
    });

};