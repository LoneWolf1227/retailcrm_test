<?php
declare(strict_types=1);

use App\Controller\AddOrderController;
use App\Controller\ViewUsersController;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->group('/users', function (Group $group) {
        $group->post('/add', [AddOrderController::class, 'add']);
    });

};