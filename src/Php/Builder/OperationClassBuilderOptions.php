<?php

namespace OpenSolid\OpenApiAssistantBundle\Php\Builder;

final readonly class OperationClassBuilderOptions
{
    public function __construct(
        public string $suffix = 'Action',
    ) {
    }
}
