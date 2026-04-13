<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering\FieldRenderers;

use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Utilities\Get;
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
            ->afterStateHydrated(function (Field $component, mixed $state) use ($field, $fieldKey): void {
                if ($state !== null || ! array_key_exists('default', $field)) {
                    return;
                }

                $component->state($field['default']);
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
     * @param  array<string, mixed>  $field
     */
    protected function applyAffixes(Field $component, array $field): Field
    {
        $ui = (array) ($field['ui'] ?? []);

        if (is_string($ui['prefix'] ?? null) && $ui['prefix'] !== '' && method_exists($component, 'prefix')) {
            $component->prefix($ui['prefix']);
        }

        if (is_string($ui['suffix'] ?? null) && $ui['suffix'] !== '' && method_exists($component, 'suffix')) {
            $component->suffix($ui['suffix']);
        }

        return $component;
    }
}
