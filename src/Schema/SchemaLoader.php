<?php

declare(strict_types=1);

namespace FormSchema\Filament\Schema;

use FormSchema\Filament\Exceptions\InvalidSchemaException;

class SchemaLoader
{
    /**
     * @param  array<string, mixed>  $schema
     */
    public function fromCanonical(array $schema): SchemaInput
    {
        if (! isset($schema['form']) || ! is_array($schema['form'])) {
            throw new InvalidSchemaException('Schema is missing a form envelope.');
        }

        /** @var array<int, array<string, mixed>> $pages */
        $pages = is_array($schema['form']['pages'] ?? null) ? array_values($schema['form']['pages']) : [];

        if ($pages === []) {
            throw new InvalidSchemaException('Schema has no pages to render.');
        }

        $fieldIndex = [];

        foreach ($pages as $page) {
            foreach (($page['sections'] ?? []) as $section) {
                foreach (($section['fields'] ?? []) as $field) {
                    $key = $field['key'] ?? null;

                    if (! is_string($key) || $key === '') {
                        continue;
                    }

                    $fieldIndex[$key] = $field;
                }
            }
        }

        return new SchemaInput($schema, $pages, $fieldIndex);
    }
}
