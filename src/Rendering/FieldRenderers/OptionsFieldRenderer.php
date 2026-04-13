<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering\FieldRenderers;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Utilities\Get;
use FormSchema\Filament\Contracts\FieldRenderer;
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

        $options = $this->normalizeOptions((array) ($properties['data'] ?? []));

        $component = match ($variant) {
            'radio', 'tabs' => Radio::make($context->dot($key))->options($options),
            'checkbox' => CheckboxList::make($context->dot($key))->options($options),
            'multi-select' => Select::make($context->dot($key))->options($options)->multiple(),
            default => Select::make($context->dot($key))->options($options),
        };

        $source = (array) ($properties['source'] ?? []);
        if ((bool) ($source['enabled'] ?? false)) {
            $component->options(function (Get $get) use ($context, $field, $options): array {
                /** @var array<string, mixed> $state */
                $state = (array) ($get($context->statePath) ?? []);

                $resolved = $context->dynamicDataResolver->resolveDynamicOptions($field, $context->schema->schema, $state);

                if ( ! is_array($resolved)) {
                    return $options;
                }

                return $this->normalizeOptions($resolved);
            });
        }

        return $this->applyCommon($component, $field, $context);
    }

    /**
     * @param  array<int|string, mixed>  $options
     * @return array<string, string>
     */
    private function normalizeOptions(array $options): array
    {
        $normalized = [];

        foreach ($options as $index => $option) {
            if (is_array($option)) {
                $optionKey = (string) ($option['key'] ?? '');

                if ('' === $optionKey) {
                    continue;
                }

                $normalized[$optionKey] = (string) ($option['value'] ?? $optionKey);

                continue;
            }

            if (is_string($index)) {
                $normalized[$index] = is_scalar($option) ? (string) $option : $index;
            }
        }

        return $normalized;
    }
}
