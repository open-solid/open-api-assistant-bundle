<?php

namespace OpenSolid\OpenApiAssistantBundle\Model;

final readonly class FlashMessage
{
    public function __construct(
        public string $title,
        public string $body,
    ) {
    }
}
