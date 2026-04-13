<?php

declare(strict_types=1);

namespace FormSchema\Filament\Schema;

class SchemaInput
{
    /**
     * @param  array<string, mixed>  $schema
     * @param  array<int, array<string, mixed>>  $pages
     * @param  array<string, array<string, mixed>>  $fieldIndex
     */
    public function __construct(
        public readonly array $schema,
        public readonly array $pages,
        public readonly array $fieldIndex,
    ) {}
}
