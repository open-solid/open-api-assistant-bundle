<?php

use OpenSolid\OpenApiAssistantBundle\Controller\OpenApiAssistantAction;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes
        ->add('openapi_assistant', '/_assistant')
            ->controller([OpenApiAssistantAction::class, '__invoke'])
            ->methods(['GET', 'POST'])
    ;
};
