<?php

namespace OpenSolid\OpenApiAssistantBundle\OpenApi\Builder;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use OpenApi\Annotations\Components;
use OpenApi\Annotations\Items;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use OpenSolid\OpenApiAssistantBundle\OpenApi\OpenApiSpec;

readonly class SchemaBuilder
{
    private Inflector $inflector;

    public function __construct()
    {
        $this->inflector = InflectorFactory::create()->build();
    }

    public function build(string $name, string $payload, OpenApi $openApi): void
    {
        if (Generator::isDefault($openApi->components)) {
            $openApi->components = new Components([]);
            $openApi->components->schemas = [];
        }

        $this->process($name, json_decode($payload), $openApi->components);
    }

    protected function process(string $name, mixed $payload, Components $components): Schema
    {
        $schemaName = ucfirst($name);

        if (is_object($payload)) {
            $schema = new Schema(['type' => 'object']);
            $schema->schema = $schemaName;
            $schema->properties = [];
        } elseif (is_array($payload)) {
            $schema = new Schema(['type' => 'array']);
        } else {
            throw new \InvalidArgumentException(sprintf('Unsupported type "%s"', gettype($payload)));
        }

        foreach ($payload as $key => $value) {
            if ('array' === $schema->type) {
                if (is_scalar($value)) {
                    $schema->items = new Items([]);
                    $schema->items->type = gettype($value);
                } elseif (is_object($value)) {
                    $schema->items = new Items([]);
                    $schema->items->type = 'object';
                    $nestedSchema = $this->process($schemaName, $value, $components);
                    $schema->items->ref = '#/components/schemas/'.$nestedSchema->schema;
                }

                return $schema;
            }

            if (is_object($value)) {
                $nestedSchema = $this->process($schemaName.ucfirst($key), $value, $components);
                $property = new Property(['property' => $key]);
                $property->type = 'object';
                $property->ref = '#/components/schemas/'.$nestedSchema->schema;
            } elseif (is_array($value)) {
                $property = new Property(['property' => $key]);
                if ([] === $value) {
                    $property->type = 'array';
                    $property->items = new Items([]);
                    $property->items->type = 'string';
                } elseif (is_object($value[0])) {
                    $property->type = 'array';
                    $property->items = new Items([]);
                    $property->items->type = 'object';
                    $nestedSchema = $this->process($schemaName.ucfirst($this->inflector->singularize($key)), $value[0], $components);
                    $property->items->ref = '#/components/schemas/'.$nestedSchema->schema;
                } elseif (is_string($value[0]) || is_int($value[0])) {
                    $property->type = gettype($value[0]);
                    $property->enum = $value;
                } else {
                    // unknown type, skip
                    continue;
                }
            } elseif (is_scalar($value)) {
                $property = new Property(['property' => $key]);
                $property->type = OpenApiSpec::getDataType($value);
                if ($format = OpenApiSpec::getDataFormat($value)) {
                    $property->format = $format;
                }
                if ($pattern = OpenApiSpec::getDataPattern($value)) {
                    $property->pattern = $pattern;
                }
                if (!$pattern && 'password' !== $format) {
                    $property->example = $value;
                }
            } else {
                // unknown type, skip
                continue;
            }

            $schema->properties[$key] = $property;
        }

        if (!isset($components->schemas[$schemaName])) {
            $components->schemas[$schemaName] = $schema;
        }

        return $schema;
    }
}
