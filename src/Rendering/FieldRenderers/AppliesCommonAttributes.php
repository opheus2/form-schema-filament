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

        return $component
            ->label(is_string($field['label'] ?? null) ? $field['label'] : null)
            ->helperText(is_string($field['help_text'] ?? null) ? $field['help_text'] : null)
            ->required((bool) ($field['required'] ?? false))
            ->readOnly((bool) ($field['readonly'] ?? false))
            ->hiddenLabel(false)
            ->placeholder(is_string($field['placeholder'] ?? null) ? $field['placeholder'] : null)
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
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function applyAffixes(Field $component, array $field): Field
    {
        $ui = (array) ($field['ui'] ?? []);

        if (is_string($ui['prefix'] ?? null) && $ui['prefix'] !== '') {
            $component->prefix($ui['prefix']);
        }

        if (is_string($ui['suffix'] ?? null) && $ui['suffix'] !== '') {
            $component->suffix($ui['suffix']);
        }

        return $component;
    }
}
