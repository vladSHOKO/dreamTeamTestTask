<?php

use Bitrix\Main\Routing\RoutingConfigurator;
use Dreamteam\Test\Controller\NewsController;

return static function (RoutingConfigurator $routes): void {
    $routes->prefix('api/news')->group(static function (RoutingConfigurator $routes): void {
        $routes->post('add', [NewsController::class, 'add']);
        $routes->get('list', [NewsController::class, 'list']);
        $routes->get('{id}', [NewsController::class, 'detail'])->where('id', '[0-9]+');
    });
};
