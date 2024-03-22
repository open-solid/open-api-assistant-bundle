<?php

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
    $definition
        ->rootNode()
            ->children()
                ->arrayNode('paths')
                    ->children()
                        ->scalarNode('controller')->defaultValue('<resource>/Controller/<method>/')->end()
                        ->scalarNode('request')->defaultValue('<resource>/Controller/<method>/')->end()
                        ->scalarNode('response')->defaultValue('<resource>/Controller/<method>/')->end()
                    ->end()
                ->end()
            ->end()
        ->end()
    ;
};
