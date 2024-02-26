<?php

use OpenSolid\OpenApiAssistantBundle\Controller\OpenApiAssistantAction;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container
        ->services()
            ->set(OpenApiAssistantAction::class)
                ->autowire()
                ->autoconfigure()
    ;
};
