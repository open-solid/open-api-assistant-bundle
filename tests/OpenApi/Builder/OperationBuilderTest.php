<?php

namespace OpenSolid\OpenApiAssistantBundle\Tests\OpenApi\Builder;

use OpenApi\Annotations\OpenApi;
use OpenSolid\OpenApiAssistantBundle\OpenApi\Builder\OperationBuilder;
use OpenSolid\OpenApiAssistantBundle\OpenApi\Builder\SchemaBuilder;
use OpenSolid\OpenApiAssistantBundle\Request\HttpRequestInterpreter;
use OpenSolid\OpenApiAssistantBundle\Tests\AbstractBuilderTestCase;

class OperationBuilderTest extends AbstractBuilderTestCase
{
    public function testGetCollectionBuild(): void
    {
        $openApi = new OpenApi(['openapi' => '3.1.0']);
        $response = $this->loadFileContent('open-api/builder/operation/get_collection/users_response.json');

        $operationBuilder = new OperationBuilder(new SchemaBuilder(), new HttpRequestInterpreter());
        $operationBuilder->build('get', '/users', null, $response, $openApi);

        $this->assertTrue($openApi->validate());
        $this->assertSameResult($openApi->toYaml(), 'open-api/builder/operation/get_collection/users_spec.yaml');
    }

    public function testGetItemBuild(): void
    {
        $openApi = new OpenApi(['openapi' => '3.1.0']);
        $response = $this->loadFileContent('open-api/builder/operation/get/user_response.json');

        $operationBuilder = new OperationBuilder(new SchemaBuilder(), new HttpRequestInterpreter());
        $operationBuilder->build('get', '/users/{id}', null, $response, $openApi);

        $this->assertTrue($openApi->validate());
        $this->assertSameResult($openApi->toYaml(), 'open-api/builder/operation/get/user_spec.yaml');
    }

    public function testPostBuild(): void
    {
        $openApi = new OpenApi(['openapi' => '3.1.0']);
        $request = $this->loadFileContent('open-api/builder/operation/post/user_request.json');
        $response = $this->loadFileContent('open-api/builder/operation/post/user_response.json');

        $operationBuilder = new OperationBuilder(new SchemaBuilder(), new HttpRequestInterpreter());
        $operationBuilder->build('post', '/users', $request, $response, $openApi);

        $this->assertTrue($openApi->validate());
        $this->assertSameResult($openApi->toYaml(), 'open-api/builder/operation/post/user_spec.yaml');
    }

    public function testPutBuild(): void
    {
        $openApi = new OpenApi(['openapi' => '3.1.0']);
        $request = $this->loadFileContent('open-api/builder/operation/put/user_request.json');
        $response = $this->loadFileContent('open-api/builder/operation/put/user_response.json');

        $operationBuilder = new OperationBuilder(new SchemaBuilder(), new HttpRequestInterpreter());
        $operationBuilder->build('put', '/users/{id}', $request, $response, $openApi);

        $this->assertTrue($openApi->validate());
        $this->assertSameResult($openApi->toYaml(), 'open-api/builder/operation/put/user_spec.yaml');
    }

    public function testPatchBuild(): void
    {
        $openApi = new OpenApi(['openapi' => '3.1.0']);
        $request = $this->loadFileContent('open-api/builder/operation/patch/user_request.json');
        $response = $this->loadFileContent('open-api/builder/operation/patch/user_response.json');

        $operationBuilder = new OperationBuilder(new SchemaBuilder(), new HttpRequestInterpreter());
        $operationBuilder->build('patch', '/users/{id}', $request, $response, $openApi);

        $this->assertTrue($openApi->validate());
        $this->assertSameResult($openApi->toYaml(), 'open-api/builder/operation/patch/user_spec.yaml');
    }

    public function testDeleteBuild(): void
    {
        $openApi = new OpenApi(['openapi' => '3.1.0']);

        $operationBuilder = new OperationBuilder(new SchemaBuilder(), new HttpRequestInterpreter());
        $operationBuilder->build('delete', '/users/{id}', null, null, $openApi);

        $this->assertTrue($openApi->validate());
        $this->assertSameResult($openApi->toYaml(), 'open-api/builder/operation/delete/user_spec.yaml');
    }
}
