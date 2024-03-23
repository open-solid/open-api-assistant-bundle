<?php

use OpenSolid\OpenApiAssistantBundle\OpenApiAssistantBundle;
use OpenSolid\OpenApiBundle\OpenApiBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

require dirname(__DIR__).'/vendor/autoload.php';

class Application extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new OpenApiBundle(),
            new OpenApiAssistantBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'S0ME_SECRET',
            'router' => [
                'resource' => __DIR__.'/config/routes.php',
                'type' => 'php',
            ],
            'session' => true,
        ]);

        $container->extension('openapi', [
            'paths' => [
                '%kernel.project_dir%/config/openapi.yaml',
            ],
        ]);

        $container->import('config/openapi_assistant.yaml');
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/openapiassistant/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/openapiassistant/logs';
    }
}

$app = new Application('dev', true);
$request = Request::createFromGlobals();
$response = $app->handle($request);
$response->send();
$app->terminate($request, $response);
