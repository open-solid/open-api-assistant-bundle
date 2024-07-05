<?php

namespace OpenSolid\OpenApiAssistantBundle;

use OpenSolid\OpenApiAssistant\OpenApi\Builder\SchemaBuilderOptions;
use OpenSolid\OpenApiAssistant\Php\Builder\OperationClassBuilderOptions;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class OpenApiAssistantBundle extends AbstractBundle
{
    protected string $extensionAlias = 'openapi_assistant';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->services()
            ->set(OperationClassBuilderOptions::class)
                ->args([
                    $config['operation']['controller']['suffix'],
                ])
            ->set('openapi_assistant.operation_builder_options.request', SchemaBuilderOptions::class)
                ->args([
                    $config['operation']['request']['write_only'],
                    false,
                    $config['operation']['request']['suffix'],
                ])
            ->set('openapi_assistant.operation_builder_options.response', SchemaBuilderOptions::class)
                ->args([
                    false,
                    $config['operation']['response']['read_only'],
                    $config['operation']['response']['suffix'],
                ])
        ;

        $container->import('../config/services.php');
    }
}
