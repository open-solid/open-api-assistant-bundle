<?php

namespace OpenSolid\OpenApiAssistantBundle\Tests\Builder;

use OpenSolid\OpenApiAssistantBundle\Builder\SchemaClassBuilder;
use OpenSolid\OpenApiAssistantBundle\Tests\AbstractBuilderTestCase;

class SchemaClassBuilderTest extends AbstractBuilderTestCase
{
    public function testBuild(): void
    {
        $openApi = $this->loadOpenApiSpec('builder/schema/user_spec.yaml');
        $namespace = 'App\\Schema';
        $schemaClassBuilder = new SchemaClassBuilder();

        $this->assertSameResult(
            $schemaClassBuilder->build($namespace, $openApi->components->schemas['User']),
            'builder/schema/User.php.expected',
        );

        $this->assertSameResult(
            $schemaClassBuilder->build($namespace, $openApi->components->schemas['UserAddress']),
            'builder/schema/UserAddress.php.expected',
        );

        $this->assertSameResult(
            $schemaClassBuilder->build($namespace, $openApi->components->schemas['UserContact']),
            'builder/schema/UserContact.php.expected',
        );
    }
}
