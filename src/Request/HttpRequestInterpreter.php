<?php

namespace OpenSolid\OpenApiAssistantBundle\Request;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use RuntimeException;

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

    public function getContentItemsType(string $payload): string
    {
        if ('array' !== $this->getContentType($payload)) {
            throw new RuntimeException('Expected array payload, object given.');
        }

        $data = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);

        return gettype(current($data));
    }

    public function getResourceName(string $uri, bool $main = false): string
    {
        $parts = array_filter(explode('/', $uri), static fn (string $part): bool => '' !== $part);

        if ([] === $parts) {
            throw new \InvalidArgumentException(sprintf('Unable to guess resource name from URI "%s"', $uri));
        }

        $resources = [];
        do {
            do {
                $resource = array_pop($parts);
            } while (null !== $resource && $resource[0] === '{');

            if (null !== $resource) {
                array_unshift($resources, ucfirst($this->inflector->singularize($resource)));
            }
        } while ($parts);

        if ([] === $resources) {
            throw new \InvalidArgumentException(sprintf('Unable to guess resource name from URI "%s"', $uri));
        }

        if ($main) {
            return $resources[0];
        }

        return implode('', $resources);
    }
}
