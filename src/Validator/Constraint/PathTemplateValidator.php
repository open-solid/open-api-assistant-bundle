<?php

namespace OpenSolid\OpenApiAssistantBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PathTemplateValidator extends ConstraintValidator
{
    public const PATTERN = '/^(\/|[a-z0-9-]+|{[a-zA-Z0-9_|-]+})+$/';
    public const DUPLICATED_PATTERN = '/{([\w|-]+)}/';
    public const VAR_PATTERN = '/^[a-zA-Z_]\w*$/';

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PathTemplate) {
            throw new UnexpectedTypeException($constraint, PathTemplate::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        // Check path template.
        if (!preg_match(self::PATTERN, $value)) {
            $this->context->addViolation($constraint->invalidPathMessage);
        }

        // Extract placeholders.
        preg_match_all(self::DUPLICATED_PATTERN, $value, $matches);

        // Contains all the captured group matches.
        $placeholders = $matches[1];

        // Check for duplicates by comparing the count of unique placeholders to the total count.
        if (count($placeholders) !== count(array_unique($placeholders))) {
            $this->context->addViolation($constraint->duplicatedPlaceholderMessage);
        }

        // Check for invalid placeholders.
        foreach ($placeholders as $placeholder) {
            if (!preg_match(self::VAR_PATTERN, $placeholder)) {
                $this->context->addViolation($constraint->invalidPlaceholderMessage);
            }
        }
    }
}
