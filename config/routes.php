<?php

use OpenSolid\OpenApiAssistantBundle\Controller\OpenApiAssistantAction;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes
        ->add('openapi_assistant', '/_assistant')
            ->controller([OpenApiAssistantAction::class, 'index'])
            ->methods(['GET', 'POST'])

        ->add('openapi_assistant_generate', '/_assistant/generate')
            ->controller([OpenApiAssistantAction::class, 'generate'])
            ->methods(['POST'])
    ;
};
