<?php

declare(strict_types=1);

namespace FormSchema\Filament\Exceptions;

use Throwable;
use RuntimeException;

class DynamicDataRequestException extends RuntimeException
{
    public static function fromHttpFailure(string $method, string $endpoint, int $status): self
    {
        return new self(sprintf(
            'Dynamic request failed [%s %s] with status %d.',
            $method,
            $endpoint,
            $status,
        ));
    }

    public static function fromThrowable(string $method, string $endpoint, Throwable $exception): self
    {
        return new self(sprintf(
            'Dynamic request failed [%s %s]: %s',
            $method,
            $endpoint,
            $exception->getMessage(),
        ), previous: $exception);
    }
}
