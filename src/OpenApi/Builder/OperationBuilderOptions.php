<?php

namespace OpenSolid\OpenApiAssistantBundle\OpenApi\Builder;

final readonly class OperationBuilderOptions
{
    public function __construct(
        public SchemaBuilderOptions $request = new SchemaBuilderOptions(
            writeOnly: true,
            suffix: 'Body',
        ),
        public SchemaBuilderOptions $response = new SchemaBuilderOptions(
            readOnly: true,
            suffix: 'View',
        ),
    ) {
    }
}
