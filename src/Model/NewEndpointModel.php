<?php

namespace OpenSolid\OpenApiAssistantBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewEndpointModel
{
    #[Assert\NotBlank(message: 'The method should not be blank.')]
    public string $method;

    #[Assert\NotBlank(message: 'The URI should not be blank.')]
    public string $uri;

    #[Assert\Json(message: 'The request payload should be a valid JSON.')]
    public ?string $req = null;

    #[Assert\Json(message: 'The response payload should be a valid JSON.')]
    public ?string $res = null;
}
