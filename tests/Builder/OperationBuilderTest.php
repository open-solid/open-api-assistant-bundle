<?php

namespace OpenSolid\OpenApiAssistantBundle\Tests\Builder;

use OpenApi\Annotations\OpenApi;
use OpenSolid\OpenApiAssistantBundle\Builder\OperationBuilder;
use OpenSolid\OpenApiAssistantBundle\Builder\SchemaBuilder;

class OperationBuilderTest extends AbstractBuilderTestCase
{
    public function testGetCollectionBuild(): void
    {
        $openApi = new OpenApi(['openapi' => '3.1.0']);
        $response = $this->loadFileContent('operation_get_users_response.json');

        $operationBuilder = new OperationBuilder(new SchemaBuilder());
        $operationBuilder->build('get', '/users', null, $response, $openApi);

        $this->assertTrue($openApi->validate());
        $this->assertSameResult($openApi->toYaml(), 'operation_get_users_spec.yaml');
    }

    public function testGetItemBuild(): void
    {
        $openApi = new OpenApi(['openapi' => '3.1.0']);
        $response = $this->loadFileContent('operation_get_user_response.json');

        $operationBuilder = new OperationBuilder(new SchemaBuilder());
        $operationBuilder->build('get', '/users/{id}', null, $response, $openApi);

        $this->assertTrue($openApi->validate());
        $this->assertSameResult($openApi->toYaml(), 'operation_get_user_spec.yaml');
    }

    public function testPostBuild(): void
    {
        $openApi = new OpenApi(['openapi' => '3.1.0']);
        $request = $this->loadFileContent('operation_post_user_request.json');
        $response = $this->loadFileContent('operation_post_user_response.json');

        $operationBuilder = new OperationBuilder(new SchemaBuilder());
        $operationBuilder->build('post', '/users', $request, $response, $openApi);

        $this->assertTrue($openApi->validate());
        $this->assertSameResult($openApi->toYaml(), 'operation_post_user_spec.yaml');
    }

    public function testPutBuild(): void
    {
        $openApi = new OpenApi(['openapi' => '3.1.0']);
        $request = $this->loadFileContent('operation_put_user_request.json');
        $response = $this->loadFileContent('operation_put_user_response.json');

        $operationBuilder = new OperationBuilder(new SchemaBuilder());
        $operationBuilder->build('put', '/users/{id}', $request, $response, $openApi);

        $this->assertTrue($openApi->validate());
        $this->assertSameResult($openApi->toYaml(), 'operation_put_user_spec.yaml');
    }

    public function testPatchBuild(): void
    {
        $openApi = new OpenApi(['openapi' => '3.1.0']);
        $request = $this->loadFileContent('operation_patch_user_request.json');
        $response = $this->loadFileContent('operation_patch_user_response.json');

        $operationBuilder = new OperationBuilder(new SchemaBuilder());
        $operationBuilder->build('patch', '/users/{id}', $request, $response, $openApi);

        $this->assertTrue($openApi->validate());
        $this->assertSameResult($openApi->toYaml(), 'operation_patch_user_spec.yaml');
    }

    public function testDeleteBuild(): void
    {
        $openApi = new OpenApi(['openapi' => '3.1.0']);

        $operationBuilder = new OperationBuilder(new SchemaBuilder());
        $operationBuilder->build('delete', '/users/{id}', null, null, $openApi);

        $this->assertTrue($openApi->validate());
        $this->assertSameResult($openApi->toYaml(), 'operation_delete_user_spec.yaml');
    }
}
