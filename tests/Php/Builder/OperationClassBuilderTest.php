<?php

namespace OpenSolid\OpenApiAssistantBundle\Tests\Php\Builder;

use OpenSolid\OpenApiAssistantBundle\Php\Builder\OperationClassBuilder;
use OpenSolid\OpenApiAssistantBundle\Request\HttpRequestInterpreter;
use OpenSolid\OpenApiAssistantBundle\Tests\AbstractBuilderTestCase;

class OperationClassBuilderTest extends AbstractBuilderTestCase
{
    public function testPostBuild(): void
    {
        $openApi = $this->loadOpenApiSpec('php/builder/operation/post/user_spec.yaml');
        $operationClassBuilder = new OperationClassBuilder(new HttpRequestInterpreter());

        $code = $operationClassBuilder->build('Api\\User\\Controller\\Post', $openApi->paths['/users']->post, '/users');

        $this->assertSameResult($code, 'php/builder/operation/post/PostUserAction.php.expected');
    }

    public function testGetBuild(): void
    {
        $openApi = $this->loadOpenApiSpec('php/builder/operation/get/user_spec.yaml');
        $operationClassBuilder = new OperationClassBuilder(new HttpRequestInterpreter());

        $code = $operationClassBuilder->build('Api\\User\\Controller\\Get', $openApi->paths['/users/{id}']->get, '/users/{id}');

        $this->assertSameResult($code, 'php/builder/operation/get/GetUserAction.php.expected');
    }

    public function testGetCollectionBuild(): void
    {
        $openApi = $this->loadOpenApiSpec('php/builder/operation/get_collection/users_spec.yaml');
        $operationClassBuilder = new OperationClassBuilder(new HttpRequestInterpreter());

        $code = $operationClassBuilder->build('Api\\User\\Controller\\Get', $openApi->paths['/users']->get, '/users');

        $this->assertSameResult($code, 'php/builder/operation/get_collection/GetUsersAction.php.expected');
    }

    public function testPutBuild(): void
    {
        $openApi = $this->loadOpenApiSpec('php/builder/operation/put/user_spec.yaml');
        $operationClassBuilder = new OperationClassBuilder(new HttpRequestInterpreter());

        $code = $operationClassBuilder->build('Api\\User\\Controller\\Put', $openApi->paths['/users/{id}']->put, '/users/{id}');

        $this->assertSameResult($code, 'php/builder/operation/put/PutUserAction.php.expected');
    }

    public function testPatchBuild(): void
    {
        $openApi = $this->loadOpenApiSpec('php/builder/operation/patch/user_spec.yaml');
        $operationClassBuilder = new OperationClassBuilder(new HttpRequestInterpreter());

        $code = $operationClassBuilder->build('Api\\User\\Controller\\Patch', $openApi->paths['/users/{id}']->patch, '/users/{id}');

        $this->assertSameResult($code, 'php/builder/operation/patch/PatchUserAction.php.expected');
    }

    public function testDeleteBuild(): void
    {
        $openApi = $this->loadOpenApiSpec('php/builder/operation/delete/user_spec.yaml');
        $operationClassBuilder = new OperationClassBuilder(new HttpRequestInterpreter());

        $code = $operationClassBuilder->build('Api\\User\\Controller\\Delete', $openApi->paths['/users/{id}']->delete, '/users/{id}');

        $this->assertSameResult($code, 'php/builder/operation/delete/DeleteUserAction.php.expected');
    }
}
