<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    // импорт атрибутов контроллеров
    $routes->import("../src/Controller/", "attribute");

    // endpoint refresh-токена
    $routes->add("gesdinet_jwt_refresh_token", "/api/token/refresh")
        ->controller("gesdinet_jwt_refresh_token.controller:refreshAction")
        ->methods(["POST"]);
};
