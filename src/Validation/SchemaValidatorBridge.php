<?php

declare(strict_types=1);

namespace FormSchema\Filament\Validation;

use FormSchema\SchemaValidator;
use FormSchema\ValidationResult;

class SchemaValidatorBridge
{
    public function __construct(
        private readonly SchemaValidator $validator = new SchemaValidator(),
    ) {}

    /**
     * @param  array<string, mixed>  $schema
     */
    public function validate(array $schema): ValidationResult
    {
        return $this->validator->validate($schema);
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    public function assertValid(array $schema): void
    {
        $this->validator->assertValid($schema);
    }
}
