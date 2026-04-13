<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering\FieldRenderers;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use FormSchema\Filament\Contracts\FieldRenderer;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Component;
use FormSchema\Filament\Rendering\RendererContext;

class OptionsFieldRenderer implements FieldRenderer
{
    use AppliesCommonAttributes;

    /**
     * @param  array<string, mixed>  $field
     */
    public function render(array $field, RendererContext $context): Component
    {
        $key = (string) ($field['key'] ?? '');
        $properties = (array) ($field['option_properties'] ?? []);
        $variant = (string) ($properties['type'] ?? 'select');

        $options = [];
        foreach ((array) ($properties['data'] ?? []) as $option) {
            if (! is_array($option)) {
                continue;
            }

            $optionKey = (string) ($option['key'] ?? '');
            if ($optionKey === '') {
                continue;
            }

            $options[$optionKey] = (string) ($option['value'] ?? $optionKey);
        }

        $component = match ($variant) {
            'radio', 'tabs' => Radio::make($context->dot($key))->options($options),
            'checkbox' => CheckboxList::make($context->dot($key))->options($options),
            'multi-select' => Select::make($context->dot($key))->options($options)->multiple(),
            default => Select::make($context->dot($key))->options($options),
        };

        return $this->applyCommon($component, $field, $context);
    }
}
