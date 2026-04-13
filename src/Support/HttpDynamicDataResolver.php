<?php

declare(strict_types=1);

namespace FormSchema\Filament\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use FormSchema\Filament\Contracts\DynamicDataResolver;

class HttpDynamicDataResolver implements DynamicDataResolver
{
    public function resolveDynamicOptions(array $field, array $schema, array $state): array|null
    {
        $source = (array) data_get($field, 'option_properties.source', []);

        if (! $this->isEnabled($source) || ! $this->passesWhen((array) ($source['when'] ?? []), $state)) {
            return null;
        }

        $response = $this->request($source, $state);

        if (! is_array($response)) {
            return null;
        }

        $itemsPath = (string) ($source['items_path'] ?? 'data');
        $keyPath = (string) ($source['key_path'] ?? 'key');
        $valuePath = (string) ($source['value_path'] ?? 'value');

        /** @var mixed $items */
        $items = data_get($response, $itemsPath);

        if (! is_array($items)) {
            return [];
        }

        $resolved = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $key = data_get($item, $keyPath);

            if (! is_scalar($key) || (string) $key === '') {
                continue;
            }

            $value = data_get($item, $valuePath, $key);
            $resolved[(string) $key] = is_scalar($value) ? (string) $value : (string) $key;
        }

        return $resolved;
    }

    public function resolveAutofill(array $field, array $schema, array $state, mixed $value): ?array
    {
        $config = (array) ($field['autofill'] ?? []);

        if (! $this->isEnabled($config) || ! $this->passesWhen((array) ($config['when'] ?? []), $state)) {
            return null;
        }

        $response = $this->request($config, $state);

        return is_array($response) ? $response : null;
    }

    public function resolveValidationResponse(array $field, array $schema, array $state, mixed $value): ?array
    {
        $config = (array) ($field['validation_response'] ?? []);

        if (! $this->isEnabled($config) || ! $this->passesWhen((array) ($config['when'] ?? []), $state)) {
            return null;
        }

        $response = $this->request($config, $state);

        return is_array($response) ? $response : null;
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>|null
     */
    private function request(array $config, array $state): ?array
    {
        $endpoint = (string) ($config['endpoint'] ?? '');

        if ($endpoint === '') {
            return null;
        }

        $method = strtoupper((string) ($config['method'] ?? 'GET'));
        $headers = $this->pairListToMap((array) ($config['headers'] ?? []), $state);
        $params = $this->pairListToMap((array) ($config['params'] ?? []), $state);

        try {
            $request = Http::withHeaders($headers);
            $response = $method === 'POST'
                ? $request->post($endpoint, $params)
                : $request->get($endpoint, $params);

            if (! $response->successful()) {
                return null;
            }

            $json = $response->json();

            return is_array($json) ? $json : null;
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * @param  array<int, mixed>  $pairs
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    private function pairListToMap(array $pairs, array $state): array
    {
        $result = [];

        foreach ($pairs as $pair) {
            if (! is_array($pair)) {
                continue;
            }

            $key = (string) ($pair['key'] ?? '');

            if ($key === '') {
                continue;
            }

            $result[$key] = $this->resolveValue($pair['value'] ?? null, $state);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function resolveValue(mixed $value, array $state): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if (! preg_match('/^\{field:([^}]+)\}$/', $value, $matches)) {
            return $value;
        }

        return data_get($state, $matches[1]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function isEnabled(array $config): bool
    {
        return (bool) ($config['enabled'] ?? false);
    }

    /**
     * @param  array<string, mixed>  $when
     * @param  array<string, mixed>  $state
     */
    private function passesWhen(array $when, array $state): bool
    {
        $allConditions = Arr::where((array) ($when['all'] ?? []), fn (mixed $item): bool => is_array($item));

        foreach ($allConditions as $condition) {
            if (! $this->passesCondition((array) $condition, $state)) {
                return false;
            }
        }

        $anyConditions = Arr::where((array) ($when['any'] ?? []), fn (mixed $item): bool => is_array($item));

        if ($anyConditions === []) {
            return true;
        }

        foreach ($anyConditions as $condition) {
            if ($this->passesCondition((array) $condition, $state)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $condition
     * @param  array<string, mixed>  $state
     */
    private function passesCondition(array $condition, array $state): bool
    {
        $key = (string) ($condition['key'] ?? '');

        if ($key === '') {
            return true;
        }

        $operator = (string) ($condition['operator'] ?? 'is');
        $actual = data_get($state, $key);
        $expected = $condition['value'] ?? null;

        return match ($operator) {
            'is' => $actual == $expected,
            'is_not' => $actual != $expected,
            'contains' => is_string($actual) && is_string($expected) && str_contains($actual, $expected),
            'not_contains' => is_string($actual) && is_string($expected) && ! str_contains($actual, $expected),
            'starts_with' => is_string($actual) && is_string($expected) && str_starts_with($actual, $expected),
            'not_starts_with' => is_string($actual) && is_string($expected) && ! str_starts_with($actual, $expected),
            'ends_with' => is_string($actual) && is_string($expected) && str_ends_with($actual, $expected),
            'not_ends_with' => is_string($actual) && is_string($expected) && ! str_ends_with($actual, $expected),
            'empty' => blank($actual),
            'not_empty' => filled($actual),
            'length_gte' => mb_strlen((string) ($actual ?? '')) >= (int) $expected,
            'length_lte' => mb_strlen((string) ($actual ?? '')) <= (int) $expected,
            default => true,
        };
    }
}
