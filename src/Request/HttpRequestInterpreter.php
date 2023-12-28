<?php

namespace OpenSolid\OpenApiAssistantBundle\Request;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;

final readonly class HttpRequestInterpreter
{
    private Inflector $inflector;

    public function __construct()
    {
        $this->inflector = InflectorFactory::create()->build();
    }

    public function getSuccessStatusCode(string $method, bool $hasResponse = true): int
    {
        return match($method) {
            'post' => 201,
            'delete' => $hasResponse ? 200 : 204,
            default => 200,
        };
    }

    public function getContentType(string $payload): string
    {
        return '[' === $payload[0] ? 'array' : 'object';
    }

    public function getResourceName(string $uri): string
    {
        $parts = array_filter(explode('/', $uri), static fn (string $part): bool => '' !== $part);

        if ([] === $parts) {
            throw new \InvalidArgumentException(sprintf('Unable to guess resource name from URI "%s"', $uri));
        }

        $resource = array_shift($parts);

        return ucfirst($this->inflector->singularize($resource));
    }
}
