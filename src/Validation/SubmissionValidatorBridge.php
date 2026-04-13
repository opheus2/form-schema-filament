<?php

declare(strict_types=1);

namespace FormSchema\Filament\Validation;

use FormSchema\SubmissionValidator;
use FormSchema\ValidationResult;

class SubmissionValidatorBridge
{
    public function __construct(
        private readonly SubmissionValidator $validator = new SubmissionValidator(),
    ) {}

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $replacements
     * @param  array<string, mixed>  $runtimeValidations
     */
    public function validate(array $schema, array $payload, array $replacements = [], array $runtimeValidations = []): ValidationResult
    {
        return $this->validator->validate($schema, $payload, $replacements, $runtimeValidations);
    }
}
