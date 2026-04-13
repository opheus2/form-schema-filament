<?php

declare(strict_types=1);

namespace FormSchema\Filament\Exceptions;

use RuntimeException;

class InvalidSchemaException extends RuntimeException
{
    /**
     * @param  array<string, array<int, string>>  $errors
     */
    public static function fromErrors(array $errors): self
    {
        return new self('Invalid Form Schema: ' . json_encode($errors, JSON_THROW_ON_ERROR));
    }
}
