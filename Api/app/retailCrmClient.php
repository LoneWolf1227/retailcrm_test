<?php

declare(strict_types=1);

use DI\Container;
use Psr\Container\ContainerInterface;
use RetailCrm\Api\Factory\SimpleClientFactory;

return function (Container $container) {
    $container->set(SimpleClientFactory::class, function (ContainerInterface $container) {
        $settings = $container->get('settings')['retailCrmClient'];

        return SimpleClientFactory::createClient($settings['apiUrl'], $settings['apiKey']);
    });
};
