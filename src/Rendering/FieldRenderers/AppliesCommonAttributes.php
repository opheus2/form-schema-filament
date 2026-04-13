<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering\FieldRenderers;

use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use FormSchema\Filament\Rendering\RendererContext;

trait AppliesCommonAttributes
{
    /**
     * @param  array<string, mixed>  $field
     */
    protected function applyCommon(Field $component, array $field, RendererContext $context): Field
    {
        $fieldKey = (string) ($field['key'] ?? '');
        $isReadonly = (bool) ($field['readonly'] ?? false);

        $component
            ->label(is_string($field['label'] ?? null) ? $field['label'] : null)
            ->helperText(is_string($field['help_text'] ?? null) ? $field['help_text'] : null)
            ->required((bool) ($field['required'] ?? false))
            ->hiddenLabel(false)
            ->default($field['default'] ?? null)
            ->live()
            ->visible(function (Get $get) use ($field, $context): bool {
                /** @var array<string, mixed> $state */
                $state = (array) ($get($context->statePath) ?? []);

                return $context->conditionEngine->isFieldVisible($field, $context->schema, $state);
            })
            ->afterStateHydrated(function (Field $component, mixed $state) use ($field): void {
                if (null !== $state || ! array_key_exists('default', $field)) {
                    return;
                }

                $component->state($field['default']);
            })
            ->afterStateUpdated(function (Get $get, Set $set) use ($field, $fieldKey, $context): void {
                if ('' === $fieldKey) {
                    return;
                }

                /** @var array<string, mixed> $state */
                $state = (array) ($get($context->statePath) ?? []);
                $value = data_get($state, $fieldKey);

                $autofill = (array) ($field['autofill'] ?? []);

                if ((bool) ($autofill['enabled'] ?? false)) {
                    $response = $context->dynamicDataResolver->resolveAutofill($field, $context->schema->schema, $state, $value);
                    $this->applyMapTargets($response, (array) ($autofill['map'] ?? []), $set, $context);
                }

                $validationResponse = (array) ($field['validation_response'] ?? []);

                if ((bool) ($validationResponse['enabled'] ?? false)) {
                    $response = $context->dynamicDataResolver->resolveValidationResponse($field, $context->schema->schema, $state, $value);
                    $this->applyMapTargets($response, (array) ($validationResponse['map'] ?? []), $set, $context);
                }
            });

        if (is_string($field['placeholder'] ?? null) && method_exists($component, 'placeholder')) {
            $component->placeholder($field['placeholder']);
        }

        if ($isReadonly) {
            if (method_exists($component, 'readOnly')) {
                $component->readOnly(true);
            } elseif (method_exists($component, 'disabled')) {
                $component->disabled(true);
            }
        }

        return $component;
    }

    /**
     * @param  array<string, mixed>|null  $response
     * @param  array<int, mixed>  $mappings
     */
    private function applyMapTargets(?array $response, array $mappings, Set $set, RendererContext $context): void
    {
        if ( ! is_array($response) || [] === $mappings) {
            return;
        }

        foreach ($mappings as $mapping) {
            if ( ! is_array($mapping)) {
                continue;
            }

            $target = (string) ($mapping['target'] ?? '');
            $path = (string) ($mapping['path'] ?? '');

            if ('' === $target || '' === $path || str_contains($target, ':')) {
                continue;
            }

            $value = data_get($response, $path);

            if (null === $value) {
                continue;
            }

            $set($context->dot($target), $value);
        }
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function applyAffixes(Field $component, array $field): Field
    {
        $ui = (array) ($field['ui'] ?? []);

        if (is_string($ui['prefix'] ?? null) && '' !== $ui['prefix'] && method_exists($component, 'prefix')) {
            $component->prefix($ui['prefix']);
        }

        if (is_string($ui['suffix'] ?? null) && '' !== $ui['suffix'] && method_exists($component, 'suffix')) {
            $component->suffix($ui['suffix']);
        }

        return $component;
    }
}
