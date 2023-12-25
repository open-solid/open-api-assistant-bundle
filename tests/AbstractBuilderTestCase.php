<?php

namespace OpenSolid\OpenApiAssistantBundle\Tests;

use OpenApi\Annotations\OpenApi;
use OpenApi\Serializer;
use PHPUnit\Framework\TestCase;

abstract class AbstractBuilderTestCase extends TestCase
{
    protected function loadOpenApiSpec(string $fileName): OpenApi
    {
        /** @var OpenApi $openApi */
        $openApi = (new Serializer())->deserializeFile(__DIR__.'/Data/'.$fileName);
        $this->assertTrue($openApi->validate());

        return $openApi;
    }

    protected function loadFileContent(string $fileName): string
    {
        return file_get_contents(__DIR__.'/Data/'.$fileName);
    }

    protected function assertSameResult(string $actual, string $fileName, bool $save = false): void
    {
        $filename = __DIR__.'/Data/'.$fileName;

        if ($save) {
            file_put_contents($filename, $actual);
        }

        $this->assertStringEqualsFile($filename, $actual);
    }
}
