<?php

namespace OpenSolid\OpenApiAssistantBundle\Tests\Builder;

use OpenApi\Annotations\OpenApi;
use OpenSolid\OpenApiAssistantBundle\Builder\SchemaBuilder;

class SchemaBuilderTest extends AbstractBuilderTestCase
{
    public function testBuild(): void
    {
        $openApi = new OpenApi(['openapi' => '3.1.0']);
        $payload = $this->loadFileContent('schema_user_payload.json');

        $schemaBuilder = new SchemaBuilder();
        $schemaBuilder->build('User', $payload, $openApi);

        $this->assertTrue($openApi->components->validate());
        $this->assertSameResult($openApi->components->toYaml(), 'schema_user_spec.yaml');
    }
}
