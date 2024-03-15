<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('@OpenApiBundle/config/routes.php');
    $routes->import('@OpenApiAssistantBundle/config/routes.php');
};
