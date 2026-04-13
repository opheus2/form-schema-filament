<?php

declare(strict_types=1);

namespace FormSchema\Filament\Conditions;

use FormSchema\Filament\Schema\SchemaInput;

class ConditionEngine
{
    /**
     * @param  array<string, mixed>  $field
     * @param  array<string, mixed>  $state
     */
    public function isFieldVisible(array $field, SchemaInput $schema, array $state): bool
    {
        $key = is_string($field['key'] ?? null) ? $field['key'] : null;

        if ($key === null) {
            return true;
        }

        $visible = ! ((bool) ($field['hidden'] ?? false));

        if (isset($field['visible_if']) && is_array($field['visible_if'])) {
            $visible = $visible && $this->evaluateVisibleIf($field['visible_if'], $state);
        }

        foreach ($this->allConditionals($schema) as $conditional) {
            if (! $this->targetsNode($conditional, 'field', $key, null)) {
                continue;
            }

            if ($this->evaluateConditionalWhen((array) ($conditional['when'] ?? []), $state)) {
                $visible = (($conditional['then']['action'] ?? null) === 'show');
            }
        }

        return $visible;
    }

    /**
     * @param  array<string, mixed>  $section
     * @param  array<string, mixed>  $state
     */
    public function isSectionVisible(array $section, SchemaInput $schema, array $state, ?string $pageKey = null): bool
    {
        $key = is_string($section['key'] ?? null) ? $section['key'] : null;

        if ($key === null) {
            return true;
        }

        $visible = ! ((bool) ($section['hidden'] ?? false));

        foreach ($this->allConditionals($schema) as $conditional) {
            if (! $this->targetsNode($conditional, 'section', $key, $pageKey)) {
                continue;
            }

            if ($this->evaluateConditionalWhen((array) ($conditional['when'] ?? []), $state)) {
                $visible = (($conditional['then']['action'] ?? null) === 'show');
            }
        }

        return $visible;
    }

    /**
     * @param  array<string, mixed>  $page
     * @param  array<string, mixed>  $state
     */
    public function isPageVisible(array $page, SchemaInput $schema, array $state): bool
    {
        $key = is_string($page['key'] ?? null) ? $page['key'] : null;

        if ($key === null) {
            return true;
        }

        $visible = ! ((bool) ($page['hidden'] ?? false));

        foreach ($this->allConditionals($schema) as $conditional) {
            if (! $this->targetsNode($conditional, 'page', $key, null)) {
                continue;
            }

            if ($this->evaluateConditionalWhen((array) ($conditional['when'] ?? []), $state)) {
                $visible = (($conditional['then']['action'] ?? null) === 'show');
            }
        }

        return $visible;
    }

    /**
     * @param  array<string, mixed>  $visibleIf
     * @param  array<string, mixed>  $state
     */
    public function evaluateVisibleIf(array $visibleIf, array $state): bool
    {
        $all = (array) ($visibleIf['all'] ?? []);
        $any = (array) ($visibleIf['any'] ?? []);
        $not = $visibleIf['not'] ?? null;

        $allPass = true;
        foreach ($all as $condition) {
            if (! is_array($condition) || ! $this->evaluateVisibleCondition($condition, $state)) {
                $allPass = false;
                break;
            }
        }

        $anyPass = true;
        if ($any !== []) {
            $anyPass = false;
            foreach ($any as $condition) {
                if (is_array($condition) && $this->evaluateVisibleCondition($condition, $state)) {
                    $anyPass = true;
                    break;
                }
            }
        }

        $notPass = true;
        if (is_array($not) && $not !== []) {
            $notPass = ! $this->evaluateVisibleCondition($not, $state);
        }

        return $allPass && $anyPass && $notPass;
    }

    /**
     * @param  array<string, mixed>  $condition
     * @param  array<string, mixed>  $state
     */
    public function evaluateVisibleCondition(array $condition, array $state): bool
    {
        $key = (string) ($condition['key'] ?? '');
        $operator = (string) ($condition['operator'] ?? '==');

        $left = $state[$key] ?? null;

        $value = $condition['value'] ?? null;
        $valueIsFieldKey = (bool) ($condition['valueIsFieldKey'] ?? false);

        if ($valueIsFieldKey && is_string($value)) {
            $value = $state[$value] ?? null;
        }

        return $this->evaluateOperator($operator, $left, $value, $condition);
    }

    /**
     * @param  array<string, mixed>  $when
     * @param  array<string, mixed>  $state
     */
    private function evaluateConditionalWhen(array $when, array $state): bool
    {
        $fieldKey = (string) ($when['field'] ?? '');
        $operator = (string) ($when['operator'] ?? 'is');

        $left = $state[$fieldKey] ?? null;
        $right = $this->resolveReference($when['value'] ?? null, $state);

        return $this->evaluateOperator($operator, $left, $right, $when);
    }

    /**
     * @param  array<string, mixed>  $conditional
     */
    private function targetsNode(array $conditional, string $targetType, string $targetKey, ?string $pageKey): bool
    {
        $targets = (array) ($conditional['then']['targets'] ?? []);

        foreach ($targets as $target) {
            if (! is_array($target)) {
                continue;
            }

            if (($target['type'] ?? null) !== $targetType) {
                continue;
            }

            if (($target['key'] ?? null) !== $targetKey) {
                continue;
            }

            if ($targetType === 'section' && $pageKey !== null && ($target['pageKey'] ?? null) !== $pageKey) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function allConditionals(SchemaInput $schema): array
    {
        $all = [];

        foreach ($schema->pages as $page) {
            foreach ((array) ($page['conditionals'] ?? []) as $conditional) {
                if (is_array($conditional)) {
                    $all[] = $conditional;
                }
            }

            foreach ((array) ($page['sections'] ?? []) as $section) {
                foreach ((array) ($section['conditionals'] ?? []) as $conditional) {
                    if (is_array($conditional)) {
                        $all[] = $conditional;
                    }
                }

                foreach ((array) ($section['fields'] ?? []) as $field) {
                    foreach ((array) ($field['conditionals'] ?? []) as $conditional) {
                        if (is_array($conditional)) {
                            $all[] = $conditional;
                        }
                    }
                }
            }
        }

        return $all;
    }

    /**
     * @param  array<string, mixed>  $condition
     */
    private function evaluateOperator(string $operator, mixed $left, mixed $right, array $condition): bool
    {
        return match ($operator) {
            'is', '==' => $left == $right,
            'is_not', '!=' => $left != $right,
            '>', 'gt' => is_numeric($left) && is_numeric($right) && ((float) $left > (float) $right),
            '<', 'lt' => is_numeric($left) && is_numeric($right) && ((float) $left < (float) $right),
            '>=', 'gte' => is_numeric($left) && is_numeric($right) && ((float) $left >= (float) $right),
            '<=', 'lte' => is_numeric($left) && is_numeric($right) && ((float) $left <= (float) $right),
            'contains' => $this->contains($left, $right),
            'not_contains' => ! $this->contains($left, $right),
            'starts_with' => is_string($left) && is_string($right) && str_starts_with($left, $right),
            'not_starts_with' => is_string($left) && is_string($right) && ! str_starts_with($left, $right),
            'ends_with' => is_string($left) && is_string($right) && str_ends_with($left, $right),
            'not_ends_with' => is_string($left) && is_string($right) && ! str_ends_with($left, $right),
            'empty' => $this->isEmpty($left),
            'not_empty' => ! $this->isEmpty($left),
            'true' => $left === true,
            'false' => $left === false,
            'in' => is_array($right) && in_array($left, $right, true),
            'not_in' => is_array($right) && ! in_array($left, $right, true),
            'between' => $this->between($left, $right, $condition),
            'not_between' => ! $this->between($left, $right, $condition),
            'before' => $this->compareDate($left, $right, '<'),
            'after' => $this->compareDate($left, $right, '>'),
            default => false,
        };
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function resolveReference(mixed $value, array $state): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if (! preg_match('/^\{field:([^}]+)\}$/', $value, $matches)) {
            return $value;
        }

        $key = $matches[1] ?? null;

        return is_string($key) ? ($state[$key] ?? null) : $value;
    }

    private function contains(mixed $left, mixed $right): bool
    {
        if (is_array($left)) {
            return in_array($right, $left, true);
        }

        if (is_string($left) && is_string($right)) {
            return str_contains($left, $right);
        }

        return false;
    }

    private function isEmpty(mixed $value): bool
    {
        if (is_array($value)) {
            return $value === [];
        }

        return $value === null || $value === '';
    }

    /**
     * @param  array<string, mixed>  $condition
     */
    private function between(mixed $left, mixed $right, array $condition): bool
    {
        if (isset($condition['range']) && is_array($condition['range']) && count($condition['range']) === 2) {
            $right = array_values($condition['range']);
        }

        if (! is_array($right) || count($right) < 2) {
            return false;
        }

        [$min, $max] = array_values($right);

        if (is_numeric($left) && is_numeric($min) && is_numeric($max)) {
            $value = (float) $left;

            return $value >= (float) $min && $value <= (float) $max;
        }

        if (is_string($left) && is_string($min) && is_string($max)) {
            return strcmp($left, $min) >= 0 && strcmp($left, $max) <= 0;
        }

        return false;
    }

    private function compareDate(mixed $left, mixed $right, string $operator): bool
    {
        if (! is_string($left) || ! is_string($right)) {
            return false;
        }

        $leftTime = strtotime($left);
        $rightTime = strtotime($right);

        if ($leftTime === false || $rightTime === false) {
            return false;
        }

        return $operator === '<' ? $leftTime < $rightTime : $leftTime > $rightTime;
    }
}
