<?php

namespace OpenSolid\OpenApiAssistantBundle\Php\Builder;

use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\NullableType;

trait ClassBuilderTrait
{
    private function getPhpType(Schema $schema, bool $isAttributeType = false): string|NullableType|ClassConstFetch
    {
        if ((Generator::isDefault($schema->type) || $schema->type === 'object') && !Generator::isDefault($schema->ref)) {
            $type = $this->parseClassRef($schema->ref);

            if ($isAttributeType) {
                return $this->builder->classConstFetch($type, 'class');
            }
        } else {
            $type = match ($schema->type) {
                'integer' => 'int',
                'number' => 'float',
                'boolean' => 'bool',
                default => $schema->type,
            };
        }

        if (true === $schema->nullable) {
            $type = new NullableType($type);
        }

        return $type;
    }

    private function parseClassRef(string $ref): string
    {
        $parts = explode('/', $ref);

        return array_pop($parts);
    }
}
