<?php

namespace OpenSolid\OpenApiAssistantBundle\Php\Builder;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use OpenApi\Annotations\Operation;
use OpenApi\Generator;
use OpenSolid\OpenApiAssistantBundle\Php\Printer\StdPhpPrinter;
use OpenSolid\OpenApiAssistantBundle\Request\HttpRequestInterpreter;
use PhpParser\BuilderFactory;

readonly class OperationClassBuilder
{
    use ClassBuilderTrait;

    private Inflector $inflector;
    private BuilderFactory $builder;
    private StdPhpPrinter $printer;

    public function __construct(
        private HttpRequestInterpreter $httpInterpreter,
        private string $contentType = 'application/json',
    ) {
        $this->inflector = InflectorFactory::create()->build();
        $this->builder = new BuilderFactory();
        $this->printer = new StdPhpPrinter();
    }

    public function build(string $namespace, Operation $operation, string $uri): string
    {
        $rootNode = $this->builder->namespace($namespace);
        $resourceName = $this->httpInterpreter->getResourceName($uri);
        $className = $this->inflector->classify($operation->method.' '.$resourceName.' Action');
        $routeAttrName = $this->inflector->classify($operation->method);

        $useStmts = [
            $this->builder->use('OpenSolid\\OpenApiBundle\\Routing\\Attribute\\'.$routeAttrName),
        ];


        $routeAttrArgs = [$uri];

        $methodStmt = $this->builder->method('__invoke')
            ->makePublic();

        if (!Generator::isDefault($operation->parameters)) {
            array_unshift($useStmts, $this->builder->use('OpenSolid\\OpenApiBundle\\Attribute\\Path'));
            foreach ($operation->parameters as $parameter) {
                $param = $this->builder->param($parameter->name)
                    ->addAttribute($this->builder->attribute('Path'))
                    ->setType($this->getPhpType($parameter->schema))
                ;
                $methodStmt->addParam($param);
            }
        }

        if (!Generator::isDefault($operation->requestBody) && isset($operation->requestBody->content[$this->contentType]->schema)) {
            array_unshift($useStmts, $this->builder->use('OpenSolid\\OpenApiBundle\\Attribute\\Body'));
            $param = $this->builder->param('body')
                ->addAttribute($this->builder->attribute('Body'))
                ->setType($this->getPhpType($operation->requestBody->content[$this->contentType]->schema))
            ;
            $methodStmt->addParam($param);
        }

        if (!Generator::isDefault($operation->responses)) {
            foreach ($operation->responses as $response) {
                if ($response->response < 200 || $response->response >= 300) {
                    continue;
                }

                if (isset($response->content[$this->contentType]->schema)) {
                    $returnType = $this->getPhpType($response->content[$this->contentType]->schema);
                } else {
                    $returnType = 'void';
                }

                $methodStmt->setReturnType($returnType);

                if ('array' === $returnType && isset($response->content[$this->contentType]->schema->items)) {
                    $routeAttrArgs['itemsType'] = $this->getPhpType($response->content[$this->contentType]->schema->items, true);
                }

                break;
            }
        }

        $methodStmt->addAttribute($this->builder->attribute($routeAttrName, $routeAttrArgs));

        $classStmt = $this->builder->class($className)
            ->setDocComment('')
            ->addStmt($methodStmt)
        ;

        $rootNode->addStmts([
            ...$useStmts,
            $classStmt,
        ]);

        return $this->printer->prettyPrintFile([$rootNode->getNode()]);
    }
}
