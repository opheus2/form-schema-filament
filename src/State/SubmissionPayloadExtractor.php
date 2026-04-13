<?php

declare(strict_types=1);

namespace FormSchema\Filament\State;

use FormSchema\Filament\Contracts\PayloadNormalizer;

class SubmissionPayloadExtractor implements PayloadNormalizer
{
    /**
     * @param  array<int, string>  $layoutTypes
     */
    public function __construct(
        private readonly array $layoutTypes = ['divider', 'spacing', 'banner'],
    ) {}

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function extract(array $schema, array $state): array
    {
        $payload = [];

        foreach (($schema['form']['pages'] ?? []) as $page) {
            foreach (($page['sections'] ?? []) as $section) {
                foreach (($section['fields'] ?? []) as $field) {
                    if (! is_array($field)) {
                        continue;
                    }

                    $key = (string) ($field['key'] ?? '');
                    $type = (string) ($field['type'] ?? '');

                    if ($key === '' || in_array($type, $this->layoutTypes, true)) {
                        continue;
                    }

                    if (array_key_exists($key, $state)) {
                        $payload[$key] = $state[$key];
                        continue;
                    }

                    if ($type === 'hidden' && array_key_exists('default', $field)) {
                        $payload[$key] = $field['default'];
                    }
                }
            }
        }

        return $payload;
    }
}
