<?php

namespace OpenSolid\OpenApiAssistantBundle\Model;

use OpenSolid\OpenApiAssistantBundle\Validator\Constraint\PathTemplate;
use Symfony\Component\Validator\Constraints as Assert;

class NewEndpointModel
{
    #[Assert\NotBlank(message: 'Please select a valid method.')]
    public string $method;

    #[Assert\NotBlank(message: 'Please enter a path template.')]
    #[PathTemplate(
        invalidPathMessage: 'Please enter a valid path template.',
        duplicatedPlaceholderMessage: 'Please avoid duplicated placeholders.',
        invalidPlaceholderMessage: 'Please enter a valid placeholder.',
    )]
    public string $uri;

    #[Assert\Json(message: 'Please enter a valid JSON payload.')]
    public ?string $req = null;

    #[Assert\Json(message: 'Please enter a valid JSON payload.')]
    public ?string $res = null;
}
