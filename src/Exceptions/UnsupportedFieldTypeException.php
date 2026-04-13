<?php

declare(strict_types=1);

namespace FormSchema\Filament\Exceptions;

use RuntimeException;

class UnsupportedFieldTypeException extends RuntimeException
{
    public static function make(string $type): self
    {
        return new self("Unsupported schema field type [{$type}] for Filament renderer.");
    }
}
