<?php

declare(strict_types=1);

namespace FormSchema\Filament\Contracts;

interface DynamicDataResolver
{
    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>|array<int, array<string, mixed>>|null
     */
    public function resolveDynamicOptions(array $field, array $schema, array $state): ?array;

    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>|null
     */
    public function resolveAutofill(array $field, array $schema, array $state, mixed $value): ?array;

    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>|null
     */
    public function resolveValidationResponse(array $field, array $schema, array $state, mixed $value): ?array;
}
