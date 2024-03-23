<?php

namespace OpenSolid\OpenApiAssistantBundle\OpenApi\Builder;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use OpenApi\Annotations\Delete;
use OpenApi\Annotations\Get;
use OpenApi\Annotations\Items;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\Patch;
use OpenApi\Annotations\PathItem;
use OpenApi\Annotations\Post;
use OpenApi\Annotations\Put;
use OpenApi\Annotations\RequestBody;
use OpenApi\Annotations\Response;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use OpenSolid\OpenApiAssistantBundle\Request\HttpRequestInterpreter;

final readonly class OperationBuilder
{
    private Inflector $inflector;

    public function __construct(
        private SchemaBuilder $schemaBuilder,
        private HttpRequestInterpreter $httpInterpreter,
        private OperationBuilderOptions $options = new OperationBuilderOptions(),
    ) {
        $this->inflector = InflectorFactory::create()->build();
    }

    public function build(string $method, string $uri, ?string $req, ?string $res, OpenApi $openApi): void
    {
        if (Generator::isDefault($openApi->paths)) {
            $openApi->paths = [];
        }

        if (isset($openApi->paths[$uri])) {
            $pathItem = $openApi->paths[$uri];
        } else {
            $pathItem = new PathItem([]);
            $pathItem->path = $uri;
            $openApi->paths[$uri] = $pathItem;
        }

        $methodItem = $pathItem->{$method} = $this->buildMethodItem($method, $uri);

        if (str_contains($uri, '{')) {
            $this->buildParameters($uri, $methodItem);
        }

        if (null !== $req) {
            $this->buildRequestBody($method, $uri, $req, $openApi, $methodItem);
        }

        $this->buildResponse($method, $uri, $res ?? '', $openApi, $methodItem);
    }

    private function buildMethodItem(string $method, string $uri): Put|Delete|Get|Patch|Post
    {
        $operationId = md5(strtoupper($method.'::'.$uri));
        $mainTag = $this->httpInterpreter->getResourceName($uri, true);

        return match ($method) {
            'get' => new Get(['operationId' => $operationId, 'tags' => [$mainTag]]),
            'post' => new Post(['operationId' => $operationId, 'tags' => [$mainTag]]),
            'put' => new Put(['operationId' => $operationId, 'tags' => [$mainTag]]),
            'patch' => new Patch(['operationId' => $operationId, 'tags' => [$mainTag]]),
            'delete' => new Delete(['operationId' => $operationId, 'tags' => [$mainTag]]),
            default => throw new \InvalidArgumentException(sprintf('Unsupported method "%s"', $method)),
        };
    }

    private function buildParameters(string $uri, Post|Patch|Get|Delete|Put $methodItem): void
    {
        $parameters = [];
        foreach (explode('/', $uri) as $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $parameter = new Parameter([
                    'name' => strtolower(trim($part, '{}')),
                    'in' => 'path',
                    'required' => true,
                    'schema' => new Schema(['type' => 'string']),
                ]);
                $parameters[] = $parameter;
            }
        }

        if ([] === $parameters) {
            return;
        }

        $methodItem->parameters = $parameters;
    }

    private function buildRequestBody(
        string $method,
        string $uri,
        string $payload,
        OpenApi $openApi,
        Post|Patch|Get|Delete|Put $methodItem
    ): void {
        $resource = $this->httpInterpreter->getResourceName($uri);
        $name = $this->inflector->classify($method.' '.$resource.' Body');
        $this->schemaBuilder->build($name, $payload, $openApi, $this->options->request);

        $content = $this->buildContent($payload, $name);
        $requestBody = new RequestBody(['required' => true]);
        $requestBody->merge([$content]);

        $methodItem->requestBody = $requestBody;
    }

    private function buildResponse(
        string $method,
        string $uri,
        string $payload,
        OpenApi $openApi,
        Post|Patch|Get|Delete|Put $methodItem
    ): void {
        $response = new Response([
            'response' => $this->httpInterpreter->getSuccessStatusCode($method, '' !== $payload),
            'description' => 'Successful',
        ]);

        $resource = $this->httpInterpreter->getResourceName($uri);
        $name = $this->inflector->classify($method.' '.$resource.' View');

        if ('' !== $payload) {
            $this->schemaBuilder->build($name, $payload, $openApi, $this->options->response);

            $content = $this->buildContent($payload, $name);
            $response->merge([$content]);
        }

        $methodItem->responses = [$response];
    }

    private function buildContent(string $payload, string $name): MediaType
    {
        $content = new MediaType(['mediaType' => 'application/json']);
        $content->schema = new Schema([]);
        $content->schema->type = $this->httpInterpreter->getContentType($payload);
        if ('array' === $content->schema->type) {
            $content->schema->items = new Items([]);
            $content->schema->items->ref = '#/components/schemas/'.$name;
        } else {
            $content->schema->ref = '#/components/schemas/'.$name;
        }

        return $content;
    }
}
