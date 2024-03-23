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
use OpenSolid\OpenApiAssistantBundle\OpenApi\Builder\OperationBuilderOptions;
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
    public function __construct(
        private readonly OperationClassBuilderOptions $operationClassBuilderOptions,
        private readonly OperationBuilderOptions $operationBuilderOptions,
    ) {
    }

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

            return $this->render('@OpenApiAssistant/assistant/form.html.twig', [
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
        $resourceName = $interpreter->getResourceName($uri);
        $mainResourceName = $interpreter->getResourceName($uri, true);
        // TODO: read main namespace from composer.json psr-4 autoload section
        $namespace = $request->request->getString('namespace', 'Demo\\'.$mainResourceName.'\\Controller\\'.$inflector->classify($method));
        $operationClassBuilder = new OperationClassBuilder($interpreter, 'application/json', $this->operationClassBuilderOptions);
        $openApi = new OpenApi([ // TODO: read this info from config if any
            'openapi' => '3.1.0',
            'info' => new Info(['title' => 'API', 'version' => '1.0.0']),
        ]);

        // build Open API Spec
        $operationBuilder = new OperationBuilder(new SchemaBuilder(), $interpreter, $this->operationBuilderOptions);
        $operationBuilder->build($method, $uri, $req, $res, $openApi);

        try {
            $openApi->validate();
        } catch (Exception $e) {
            $this->addFlash('error', new FlashMessage(
                title: 'Validation failed!',
                body: 'Something went wrong generating the OpenAPI Spec.',
            ));

            $form->addError(new FormError($e->getMessage()));

            return $this->render('@OpenApiAssistant/assistant/form.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // generate controller content
        $classesCode = [];
        $lineNumbers = 0;
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

            $controllerClassName = $inflector->classify($operation->method.' '.$resourceName.' '.$this->operationClassBuilderOptions->suffix);
            $classesCode[$controllerClassName] = $controllerCode = $operationClassBuilder->build($namespace, $operation, $uri);
            $lineNumbers = max($lineNumbers, substr_count($controllerCode, "\n") + 2);
        }

        // generate payloads content
        if (!Generator::isDefault($openApi->components) && !Generator::isDefault($openApi->components->schemas)) {
            $schemaClassBuilder = new SchemaClassBuilder();
            foreach ($openApi->components->schemas as $schema) {
                $classesCode[$schema->schema] = $classCode = $schemaClassBuilder->build($namespace, $schema);
                $lineNumbers = max($lineNumbers, substr_count($classCode, "\n") + 2);
            }
        }

        if ('save' === $action) {
            $dir = dirname(__DIR__, 2).sprintf('/demo/src/%s/Controller/%s', $mainResourceName, $inflector->classify($method));
            if (!is_dir($dir) && !mkdir($dir, recursive: true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
            foreach ($classesCode as $name => $classCode) {
                file_put_contents($dir.sprintf('/%s.php', $name), $classCode);
            }

            $this->addFlash('success', new FlashMessage(
                title: 'Successfully generated!',
                body: 'Your new classes has been saved as files.',
            ));

            return $this->redirectToRoute('openapi_assistant');
        }

        return $this->render('@OpenApiAssistant/assistant/form.html.twig', [
            'preview' => [
                'openapi_spec' => [
                    'yaml' => $openApi->toYaml(),
                    'json' => $openApi->toJson(),
                ],
                'classes_code' => $classesCode,
                'line_numbers' => $lineNumbers,
            ],
            'request' => $request->request->all(),
            'form' => $form->createView(),
        ]);
    }
}
