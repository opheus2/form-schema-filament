<?php

declare(strict_types=1);

namespace FormSchema\Filament\Contracts;

interface ValidationRuleMapper
{
    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, array<int, string>>
     */
    public function map(array $schema, string $statePath): array;
}
