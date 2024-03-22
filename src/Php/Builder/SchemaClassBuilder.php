<?php

namespace OpenSolid\OpenApiAssistantBundle\Php\Builder;

use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use OpenSolid\OpenApiAssistantBundle\Php\Printer\StdPhpPrinter;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;

readonly class SchemaClassBuilder
{
    use ClassBuilderTrait;

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
    private StdPhpPrinter $printer;

    public function __construct()
    {
        $this->builder = new BuilderFactory();
        $this->printer = new StdPhpPrinter();
    }

    public function build(string $namespace, Schema $schema): string
    {
        $rootNode = $this->builder->namespace($namespace);

        $useStmts = [
            $this->builder->use('OpenApi\\Attributes\\Schema'),
            $this->builder->use('OpenSolid\\OpenApiBundle\\Attribute\\Property'),
        ];

        $properties = [];
        if (is_array($schema->properties)) {
            foreach ($schema->properties as $property) {
                $properties[] = $this->buildProperty($property, $useStmts, [] === $properties);
            }
        }

        $schemaAttrArgs = [];
        if (true === $schema->writeOnly) {
            $schemaAttrArgs['writeOnly'] = true;
        }

        $classStmt = $this->builder->class($schema->schema)
            ->setDocComment('')
            ->makeFinal()
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
        } elseif ($property->type === 'object' && !Generator::isDefault($property->additionalProperties)) {
            if (is_bool($property->additionalProperties)) {
                $attrArgs['additionalProperties'] = true;
            } else {
                array_unshift($useStmts, $this->builder->use('OpenApi\\Attributes\\AdditionalProperties'));
                $attrArgs['additionalProperties'] = $this->builder->new('AdditionalProperties', [
                    'type' => $property->additionalProperties->type,
                ]);
            }
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
}
