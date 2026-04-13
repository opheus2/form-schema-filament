<?php

declare(strict_types=1);

namespace FormSchema\Filament\Support;

use Throwable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use FormSchema\Filament\Contracts\DynamicDataResolver;

class HttpDynamicDataResolver implements DynamicDataResolver
{
    public function resolveDynamicOptions(array $field, array $schema, array $state): ?array
    {
        $source = (array) data_get($field, 'option_properties.source', []);

        if ( ! $this->isEnabled($source) || ! $this->passesWhen((array) ($source['when'] ?? []), $state)) {
            return null;
        }

        $response = $this->request($source, $state);

        if ( ! is_array($response)) {
            return null;
        }

        $itemsPath = (string) ($source['items_path'] ?? 'data');
        $labelPath = (string) ($source['key_path'] ?? 'value');
        $valuePath = (string) ($source['value_path'] ?? 'key');

        /** @var mixed $items */
        $items = data_get($response, $itemsPath);

        if ( ! is_array($items)) {
            return [];
        }

        $resolved = [];

        foreach ($items as $itemKey => $item) {
            $value = $this->extractOptionPart($item, $itemKey, $valuePath, false);

            if ( ! is_scalar($value) || '' === (string) $value) {
                continue;
            }

            $label = $this->extractOptionPart($item, $itemKey, $labelPath, true);
            $resolved[(string) $value] = is_scalar($label) ? (string) $label : (string) $value;
        }

        return $resolved;
    }

    public function resolveAutofill(array $field, array $schema, array $state, mixed $value): ?array
    {
        $config = (array) ($field['autofill'] ?? []);

        if ( ! $this->isEnabled($config) || ! $this->passesWhen((array) ($config['when'] ?? []), $state)) {
            return null;
        }

        $response = $this->request($config, $state);

        return is_array($response) ? $response : null;
    }

    public function resolveValidationResponse(array $field, array $schema, array $state, mixed $value): ?array
    {
        $config = (array) ($field['validation_response'] ?? []);

        if ( ! $this->isEnabled($config) || ! $this->passesWhen((array) ($config['when'] ?? []), $state)) {
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
        $endpoint = $this->resolveEndpoint((string) ($config['endpoint'] ?? ''));

        if ('' === $endpoint) {
            return null;
        }

        $method = mb_strtoupper((string) ($config['method'] ?? 'GET'));
        $headers = $this->pairListToMap((array) ($config['headers'] ?? []), $state);
        $params = $this->pairListToMap((array) ($config['params'] ?? []), $state);

        try {
            $request = Http::withHeaders($headers);
            $response = 'POST' === $method
                ? $request->post($endpoint, $params)
                : $request->get($endpoint, $params);

            if ( ! $response->successful()) {
                return null;
            }

            $json = $response->json();

            return is_array($json) ? $json : null;
        } catch (Throwable $exception) {
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
            if ( ! is_array($pair)) {
                continue;
            }

            $key = (string) ($pair['key'] ?? '');

            if ('' === $key) {
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
        if ( ! is_string($value)) {
            return $value;
        }

        if ( ! preg_match('/^\{field:([^}]+)\}$/', $value, $matches)) {
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
     * @param  int|string  $itemKey
     */
    private function extractOptionPart(mixed $item, int|string $itemKey, string $path, bool $isLabel): mixed
    {
        if ('*' === $path) {
            if ($isLabel) {
                if (is_scalar($item)) {
                    return $item;
                }

                if (is_array($item)) {
                    foreach (['label', 'name', 'value', 'title', 'key'] as $candidate) {
                        $candidateValue = data_get($item, $candidate);

                        if (is_scalar($candidateValue) && '' !== (string) $candidateValue) {
                            return $candidateValue;
                        }
                    }

                    foreach ($item as $candidateValue) {
                        if (is_scalar($candidateValue) && '' !== (string) $candidateValue) {
                            return $candidateValue;
                        }
                    }
                }

                return is_scalar($itemKey) ? $itemKey : null;
            }

            if (is_scalar($itemKey) && '' !== (string) $itemKey) {
                return $itemKey;
            }

            if (is_array($item)) {
                foreach (['key', 'id', 'code', 'value'] as $candidate) {
                    $candidateValue = data_get($item, $candidate);

                    if (is_scalar($candidateValue) && '' !== (string) $candidateValue) {
                        return $candidateValue;
                    }
                }
            }

            return is_scalar($item) ? $item : null;
        }

        if (is_array($item)) {
            return data_get($item, $path);
        }

        return null;
    }

    private function resolveEndpoint(string $endpoint): string
    {
        if ('' === $endpoint) {
            return '';
        }

        if (str_starts_with($endpoint, 'http://') || str_starts_with($endpoint, 'https://')) {
            return $endpoint;
        }

        $appUrl = config('app.url');

        if (is_string($appUrl) && '' !== $appUrl) {
            return rtrim($appUrl, '/') . '/' . ltrim($endpoint, '/');
        }

        return URL::to($endpoint);
    }

    /**
     * @param  array<string, mixed>  $when
     * @param  array<string, mixed>  $state
     */
    private function passesWhen(array $when, array $state): bool
    {
        $allConditions = Arr::where((array) ($when['all'] ?? []), fn (mixed $item): bool => is_array($item));

        foreach ($allConditions as $condition) {
            if ( ! $this->passesCondition((array) $condition, $state)) {
                return false;
            }
        }

        $anyConditions = Arr::where((array) ($when['any'] ?? []), fn (mixed $item): bool => is_array($item));

        if ([] === $anyConditions) {
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

        if ('' === $key) {
            return true;
        }

        $operator = (string) ($condition['operator'] ?? 'is');
        $actual = data_get($state, $key);
        $expected = $condition['value'] ?? null;

        return match ($operator) {
            'is' => $actual === $expected,
            'is_not' => $actual !== $expected,
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
