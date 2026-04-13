<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering\FieldRenderers;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Component;
use FormSchema\Filament\Contracts\FieldRenderer;
use FormSchema\Filament\Rendering\RendererContext;

class AddressFieldRenderer implements FieldRenderer
{
    /**
     * @param  array<string, mixed>  $field
     */
    public function render(array $field, RendererContext $context): Component
    {
        $key = (string) ($field['key'] ?? 'address');
        $addressProperties = (array) ($field['address_properties'] ?? []);

        $components = [];
        foreach ($addressProperties as $propKey => $prop) {
            if ( ! is_array($prop)) {
                continue;
            }

            $components[] = TextInput::make($context->dot($key . '.' . (string) $propKey))
                ->label((string) ($prop['label'] ?? $propKey))
                ->required((bool) ($prop['required'] ?? false));
        }

        return Section::make((string) ($field['label'] ?? $key))
            ->description(is_string($field['help_text'] ?? null) ? $field['help_text'] : null)
            ->components($components);
    }
}
