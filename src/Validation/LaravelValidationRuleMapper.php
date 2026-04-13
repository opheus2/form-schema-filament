<?php

declare(strict_types=1);

namespace FormSchema\Filament\Validation;

use FormSchema\Filament\Contracts\ValidationRuleMapper;

class LaravelValidationRuleMapper implements ValidationRuleMapper
{
    /**
     * @param  array<string, mixed>  $schema
     * @param  string  $statePath
     * @return array<string, array<int, string>>
     */
    public function map(array $schema, string $statePath): array
    {
        $rules = [];

        foreach (($schema['form']['pages'] ?? []) as $page) {
            foreach (($page['sections'] ?? []) as $section) {
                foreach (($section['fields'] ?? []) as $field) {
                    if (! is_array($field)) {
                        continue;
                    }

                    $type = (string) ($field['type'] ?? '');
                    if (in_array($type, ['divider', 'spacing', 'banner'], true)) {
                        continue;
                    }

                    $key = (string) ($field['key'] ?? '');
                    if ($key === '') {
                        continue;
                    }

                    $dotKey = $statePath . '.' . $key;

                    $fieldRules = [];
                    $fieldRules[] = (bool) ($field['required'] ?? false) ? 'required' : 'nullable';

                    $fieldRules = array_values(array_unique(array_merge(
                        $fieldRules,
                        $this->mapTypeRules($type),
                        $this->mapConstraintRules($field),
                        $this->mapValidationRules($field),
                    )));

                    if ($type === 'address') {
                        foreach ((array) ($field['address_properties'] ?? []) as $propKey => $propConfig) {
                            if (! is_array($propConfig)) {
                                continue;
                            }

                            $addressDotKey = $dotKey . '.' . $propKey;
                            $rules[$addressDotKey] = [
                                (bool) ($propConfig['required'] ?? false) ? 'required' : 'nullable',
                                'string',
                            ];
                        }

                        continue;
                    }

                    $rules[$dotKey] = $fieldRules;
                }
            }
        }

        return $rules;
    }

    /**
     * @return array<int, string>
     */
    private function mapTypeRules(string $type): array
    {
        return match ($type) {
            'number', 'rating' => ['numeric'],
            'boolean' => ['boolean'],
            'email' => ['email'],
            'url' => ['url'],
            'date' => ['date'],
            'time' => ['date_format:H:i'],
            'datetime' => ['date'],
            'tag' => ['array'],
            'options' => [],
            default => ['string'],
        };
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<int, string>
     */
    private function mapConstraintRules(array $field): array
    {
        $rules = [];
        $constraints = (array) ($field['constraints'] ?? []);
        $type = (string) ($field['type'] ?? '');

        if (isset($constraints['min_length']) && is_numeric($constraints['min_length'])) {
            $rules[] = 'min:' . (string) $constraints['min_length'];
        }

        if (isset($constraints['max_length']) && is_numeric($constraints['max_length'])) {
            $rules[] = 'max:' . (string) $constraints['max_length'];
        }

        if (isset($constraints['min']) && is_numeric($constraints['min']) && in_array($type, ['number', 'rating'], true)) {
            $rules[] = 'min:' . (string) $constraints['min'];
        }

        if (isset($constraints['max']) && is_numeric($constraints['max']) && in_array($type, ['number', 'rating'], true)) {
            $rules[] = 'max:' . (string) $constraints['max'];
        }

        if ($type === 'tag') {
            if (isset($constraints['min']) && is_numeric($constraints['min'])) {
                $rules[] = 'min:' . (string) $constraints['min'];
            }

            if (isset($constraints['max']) && is_numeric($constraints['max'])) {
                $rules[] = 'max:' . (string) $constraints['max'];
            }
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<int, string>
     */
    private function mapValidationRules(array $field): array
    {
        $mapped = [];

        foreach ((array) ($field['validations'] ?? []) as $validation) {
            if (! is_array($validation)) {
                continue;
            }

            $rule = (string) ($validation['rule'] ?? '');
            $params = array_values((array) ($validation['params'] ?? []));

            if ($rule === '') {
                continue;
            }

            if ($params === []) {
                $mapped[] = $this->mapRuleName($rule);
                continue;
            }

            if (in_array($rule, ['required_if', 'required_unless', 'required_with', 'required_with_all', 'required_without', 'required_without_all', 'in', 'not_in', 'between', 'not_between', 'starts_with', 'ends_with', 'regex', 'before', 'after', 'min', 'max', 'gt', 'gte', 'lt', 'lte'], true)) {
                $mapped[] = $this->mapRuleName($rule) . ':' . implode(',', array_map(fn (mixed $value): string => (string) $value, $params));
                continue;
            }

            $mapped[] = $this->mapRuleName($rule);
        }

        return array_values(array_filter($mapped));
    }

    private function mapRuleName(string $rule): string
    {
        return match ($rule) {
            'phone' => 'regex:/^[0-9 +().-]{6,}$/',
            'time' => 'date_format:H:i',
            'datetime' => 'date',
            default => $rule,
        };
    }
}
