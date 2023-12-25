<?php

namespace OpenSolid\OpenApiAssistantBundle\Tests\Builder;

use PHPUnit\Framework\TestCase;

abstract class AbstractBuilderTestCase extends TestCase
{
    protected function loadFileContent(string $fileName): string
    {
        return file_get_contents(__DIR__.'/InOut/'.$fileName);
    }

    protected function assertSameResult(string $actual, string $fileName, bool $save = false): void
    {
        $filename = __DIR__.'/InOut/'.$fileName;

        if ($save) {
            file_put_contents($filename, $actual);
        }

        $this->assertStringEqualsFile($filename, $actual);
    }
}
