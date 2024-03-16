<?php

namespace OpenSolid\OpenApiAssistantBundle\Controller;

use Doctrine\Inflector\InflectorFactory;
use Exception;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use OpenSolid\OpenApiAssistantBundle\Form\Type\NewEndpointType;
use OpenSolid\OpenApiAssistantBundle\Model\FlashMessage;
use OpenSolid\OpenApiAssistantBundle\Model\NewEndpointModel;
use OpenSolid\OpenApiAssistantBundle\OpenApi\Builder\OperationBuilder;
use OpenSolid\OpenApiAssistantBundle\OpenApi\Builder\SchemaBuilder;
use OpenSolid\OpenApiAssistantBundle\Php\Builder\OperationClassBuilder;
use OpenSolid\OpenApiAssistantBundle\Php\Builder\OperationClassBuilderOptions;
use OpenSolid\OpenApiAssistantBundle\Php\Builder\SchemaClassBuilder;
use OpenSolid\OpenApiAssistantBundle\Request\HttpRequestInterpreter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OpenApiAssistantAction extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        $newEndpoint = new NewEndpointModel();

        $form = $this->createForm(NewEndpointType::class, $newEndpoint)
            ->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            if ($form->getErrors(true)->count() > 0) {
                $this->addFlash('error', new FlashMessage(
                    title: 'Validation failed!',
                    body: 'Oops! It looks like there was an error with the data you entered. Please check the form errors and try again.',
                ));
            }

            return $this->render('@OpenApiAssistant/assistant.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $method = strtolower($newEndpoint->method);
        $uri = '/'.strtolower(trim($newEndpoint->uri, '/'));
        $req = $newEndpoint->req;
        $res = $newEndpoint->res;
        $action = $request->request->getString('action') ?: 'preview';

        $interpreter = new HttpRequestInterpreter();
        $inflector = InflectorFactory::create()->build();
        $operationClassBuilderOptions = new OperationClassBuilderOptions();
        $resourceName = $interpreter->getResourceName($uri);
        // TODO: read main namespace from composer.json psr-4 autoload section
        $namespace = $request->request->getString('namespace', 'Demo\\'.$resourceName.'\\Controller\\'.$inflector->classify($method));
        $operationClassBuilder = new OperationClassBuilder($interpreter, 'application/json', $operationClassBuilderOptions);
        $openApi = new OpenApi([ // TODO: read this info from config if any
            'openapi' => '3.1.0',
            'info' => new Info(['title' => 'API', 'version' => '1.0.0']),
        ]);

        // build Open API Spec
        $operationBuilder = new OperationBuilder(new SchemaBuilder(), $interpreter);
        $operationBuilder->build($method, $uri, $req, $res, $openApi);

        try {
            $openApi->validate();
        } catch (Exception $e) {
            $this->addFlash('error', new FlashMessage(
                title: 'Validation failed!',
                body: 'Something went wrong generating the OpenAPI Spec.',
            ));

            $form->addError(new FormError($e->getMessage()));

            return $this->render('@OpenApiAssistant/assistant.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // generate controller content
        $controllerClassName = '';
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

            $controllerClassName = $inflector->classify($operation->method.' '.$resourceName.' '.$operationClassBuilderOptions->suffix);
            $controllerCode = $operationClassBuilder->build($namespace, $operation, $uri);
        }

        // generate payloads content
        $payloadClassesCode = [];
        if (!Generator::isDefault($openApi->components)) {
            $schemaClassBuilder = new SchemaClassBuilder();
            foreach ($openApi->components->schemas as $schema) {
                $payloadClassesCode[$schema->schema] = $schemaClassBuilder->build($namespace, $schema);
            }
        }

        if ('generate' === $action) {
            $dir = dirname(__DIR__, 2).sprintf('/demo/src/%s/Controller/%s', $resourceName, $inflector->classify($method));
            if (!is_dir($dir) && !mkdir($dir, recursive: true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
            file_put_contents($dir.sprintf('/%s.php', $controllerClassName), $controllerCode);
            foreach ($payloadClassesCode as $name => $classCode) {
                file_put_contents($dir.sprintf('/%s.php', $name), $classCode);
            }

            $this->addFlash('success', new FlashMessage(
                title: 'Successfully generated!',
                body: 'Your new classes has been saved as files.',
            ));

            return $this->redirectToRoute('openapi_assistant');
        }

        return $this->render('@OpenApiAssistant/assistant.html.twig', [
            'preview' => [
                'openapi_spec' => $openApi->toYaml(),
                'controller_class_name' => $controllerClassName,
                'controller_code' => $controllerCode,
                'payload_classes_code' => $payloadClassesCode,
            ],
            'request' => $request->request->all(),
            'form' => $form->createView(),
        ]);
    }
}
