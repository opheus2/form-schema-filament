<?php

declare(strict_types=1);

namespace FormSchema\Filament\Contracts;

interface PayloadNormalizer
{
    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function extract(array $schema, array $state): array;
}
