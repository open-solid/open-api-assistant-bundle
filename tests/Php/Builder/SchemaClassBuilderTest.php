<?php

namespace OpenSolid\OpenApiAssistantBundle\Tests\Php\Builder;

use OpenSolid\OpenApiAssistantBundle\Php\Builder\SchemaClassBuilder;
use OpenSolid\OpenApiAssistantBundle\Tests\AbstractBuilderTestCase;

class SchemaClassBuilderTest extends AbstractBuilderTestCase
{
    public function testBuild(): void
    {
        $openApi = $this->loadOpenApiSpec('php/builder/schema/user_spec.yaml');
        $namespace = 'App\\Schema';
        $schemaClassBuilder = new SchemaClassBuilder();

        $this->assertSameResult(
            $schemaClassBuilder->build($namespace, $openApi->components->schemas['User']),
            'php/builder/schema/User.php.expected',
        );

        $this->assertSameResult(
            $schemaClassBuilder->build($namespace, $openApi->components->schemas['UserAddress']),
            'php/builder/schema/UserAddress.php.expected',
        );

        $this->assertSameResult(
            $schemaClassBuilder->build($namespace, $openApi->components->schemas['UserContact']),
            'php/builder/schema/UserContact.php.expected',
        );
    }
}
