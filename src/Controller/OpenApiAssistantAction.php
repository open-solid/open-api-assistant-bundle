<?php

namespace OpenSolid\OpenApiAssistantBundle\Controller;

use Doctrine\Inflector\InflectorFactory;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use OpenSolid\OpenApiAssistantBundle\OpenApi\Builder\OperationBuilder;
use OpenSolid\OpenApiAssistantBundle\OpenApi\Builder\SchemaBuilder;
use OpenSolid\OpenApiAssistantBundle\Php\Builder\OperationClassBuilder;
use OpenSolid\OpenApiAssistantBundle\Php\Builder\SchemaClassBuilder;
use OpenSolid\OpenApiAssistantBundle\Request\HttpRequestInterpreter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OpenApiAssistantAction extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        if (!$request->isMethod('POST')) {
            return $this->render('@OpenApiAssistant/assistant.html.twig');
        }

        $interpreter = new HttpRequestInterpreter();
        $inflector = InflectorFactory::create()->build();

        $openApi = new OpenApi([
            'openapi' => '3.1.0',
            'info' => new Info(['title' => 'API', 'version' => '1.0.0']),
        ]);
        $method = strtolower($request->request->getString('method'));
        $uri = '/'.strtolower(trim($request->request->getString('uri'), '/'));
        $req = $request->request->getString('req') ?: null;
        $res = $request->request->getString('res') ?: null;
        $namespace = $request->request->getString('namespace', 'App\\'.$interpreter->getResourceName($uri).'\\Controller\\'.$inflector->classify($method));

        $operationBuilder = new OperationBuilder(new SchemaBuilder(), $interpreter);
        $operationBuilder->build($method, $uri, $req, $res, $openApi);

        if (!$openApi->validate()) {
            throw new BadRequestHttpException('Invalid specs.');
        }

        $operationClassBuilder = new OperationClassBuilder(new HttpRequestInterpreter());

        $controllerCode = '';
        foreach ($openApi->paths as $pathItem) {
            if (!Generator::isDefault($pathItem->post)) {
                $operation = $pathItem->post;
            } elseif (!Generator::isDefault($pathItem->get)) {
                $operation = $pathItem->get;
            } elseif (!Generator::isDefault($pathItem->put)) {
                $operation = $pathItem->put;
            } elseif (!Generator::isDefault($pathItem->patch)) {
                $operation = $pathItem->patch;
            } elseif (!Generator::isDefault($pathItem->delete)) {
                $operation = $pathItem->delete;
            } else {
                continue;
            }

            $controllerCode = $operationClassBuilder->build($namespace, $operation, $uri);
        }

        $payloadClassesCode = [];
        if (!Generator::isDefault($openApi->components)) {
            $schemaClassBuilder = new SchemaClassBuilder();
            foreach ($openApi->components->schemas as $schema) {
                $payloadClassesCode[$schema->schema] = $schemaClassBuilder->build($namespace, $schema);
            }
        }

        return $this->render('@OpenApiAssistant/assistant.html.twig', [
            'openapi' => $openApi->toYaml(),
            'controller_code' => $controllerCode,
            'payload_classes_code' => $payloadClassesCode,
            'request' => $request->request->all(),
        ]);
    }
}
