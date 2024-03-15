<?php

namespace OpenSolid\OpenApiAssistantBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class PathTemplate extends Constraint
{
    public function __construct(
        public string $invalidPathMessage = 'This value is not a valid path template.',
        public string $duplicatedPlaceholderMessage = 'This value contains duplicated placeholders.',
        public string $invalidPlaceholderMessage = 'This value contains an invalid placeholder.',
    ) {
        parent::__construct();
    }
}
