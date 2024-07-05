<?php

use OpenSolid\OpenApiAssistant\OpenApi\Builder\OperationBuilderOptions;
use OpenSolid\OpenApiAssistantBundle\Controller\OpenApiAssistantAction;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container
        ->services()
            ->set(OpenApiAssistantAction::class)
                ->autowire()
                ->autoconfigure()

            ->set(OperationBuilderOptions::class)
                ->args([
                    service('openapi_assistant.operation_builder_options.request'),
                    service('openapi_assistant.operation_builder_options.response'),
                ])
    ;
};
