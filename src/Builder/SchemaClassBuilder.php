<?php

namespace OpenSolid\OpenApiAssistantBundle\Builder;

use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\PrettyPrinter\Standard;

readonly class SchemaClassBuilder
{
    private const PROPS = [
        'title',
        'description',
        'format',
        'maximum',
        'exclusiveMaximum',
        'minimum',
        'exclusiveMinimum',
        'maxLength',
        'minLength',
        'maxItems',
        'minItems',
        'uniqueItems',
        'pattern',
        'enum',
        'example',
    ];

    private BuilderFactory $builder;
    private Standard $printer;

    public function __construct()
    {
        $this->builder = new BuilderFactory();
        $this->printer = new Standard(['shortArraySyntax' => true]);
    }

    public function build(string $namespace, Schema $schema): string
    {
        $rootNode = $this->builder->namespace($namespace);

        $useStmts = [
            $this->builder->use('OpenApi\\Attributes\\Schema'),
            $this->builder->use('OpenSolid\\OpenApiBundle\\Attribute\\Property'),
        ];

        $properties = [];
        foreach ($schema->properties as $property) {
            $properties[] = $this->buildProperty($property, $useStmts, [] === $properties);
        }

        $schemaAttrArgs = [];
        if (true === $schema->writeOnly) {
            $schemaAttrArgs['writeOnly'] = true;
        }

        $classStmt = $this->builder->class($schema->schema)
            ->setDocComment('')
            ->addAttribute($this->builder->attribute('Schema', $schemaAttrArgs))
            ->addStmts($properties)
        ;
        if (true === $schema->readOnly) {
            $classStmt->makeReadonly();
        }

        $rootNode->addStmts([
            ...$useStmts,
            $classStmt,
        ]);

        return $this->printer->prettyPrintFile([$rootNode->getNode()]);
    }

    private function buildProperty(Property $property, array &$useStmts, bool $firstProp): \PhpParser\Builder\Property
    {
        $attrArgs = [];

        foreach (self::PROPS as $prop) {
            if (!Generator::isDefault($property->$prop)) {
                $attrArgs[$prop] = $property->$prop;
            }
        }

        if ($property->type === 'array' && !Generator::isDefault($property->items)) {
            array_unshift($useStmts, $this->builder->use('OpenApi\\Attributes\\Items'));
            $attrArgs['items'] = $this->builder->new('Items', [
                'type' => new Expr\ClassConstFetch(new Name($this->parseClassRef($property->items->ref)), 'class'),
            ]);
        }

        $prop = $this->builder->property($property->property)
            ->addAttribute($this->builder->attribute('Property', $attrArgs))
            ->setType($this->getPhpType($property));

        if (!$firstProp) {
            $prop->setDocComment('');
        }

        if (!Generator::isDefault($property->default)) {
            $prop->setDefault($property->default);
        }

        return $prop;
    }

    private function getPhpType(Property $property): string|NullableType
    {
        if (Generator::isDefault($property->type) && !Generator::isDefault($property->ref)) {
            $type = $this->parseClassRef($property->ref);
        } else {
            $type = match ($property->type) {
                'integer' => 'int',
                'number' => 'float',
                'boolean' => 'bool',
                default => $property->type,
            };
        }

        if (true === $property->nullable) {
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
