<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering\FieldRenderers;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use FormSchema\Filament\Contracts\FieldRenderer;
use FormSchema\Filament\Rendering\RendererContext;

class TextFieldRenderer implements FieldRenderer
{
    use AppliesCommonAttributes;

    /**
     * @param  array<string, mixed>  $field
     */
    public function render(array $field, RendererContext $context): Component
    {
        $component = $this->resolveFieldComponent($field, $context);

        return $this->applyAffixes(
            $this->applyCommon($component, $field, $context),
            $field,
        );
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function resolveFieldComponent(array $field, RendererContext $context): Field
    {
        $type = (string) ($field['type'] ?? 'text');

        if ('url' === $type) {
            return TextInput::make($context->dot((string) $field['key']))->url();
        }

        $variant = (string) (($field['text_properties']['type'] ?? 'normal'));
        $rows = (int) (($field['ui']['multiline_rows'] ?? 2));

        if (in_array($type, ['medium-text', 'long-text'], true) || in_array($variant, ['medium', 'long'], true)) {
            return Textarea::make($context->dot((string) $field['key']))->rows(max($rows, 3));
        }

        if ('short-text' === $type || 'short' === $variant) {
            return TextInput::make($context->dot((string) $field['key']));
        }

        return Textarea::make($context->dot((string) $field['key']))->rows(max($rows, 2));
    }
}
