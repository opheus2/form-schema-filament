<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering\FieldRenderers;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use FormSchema\Filament\Contracts\FieldRenderer;
use FormSchema\Filament\Rendering\RendererContext;

class NumberFieldRenderer implements FieldRenderer
{
    use AppliesCommonAttributes;

    /**
     * @param  array<string, mixed>  $field
     */
    public function render(array $field, RendererContext $context): Component
    {
        $component = TextInput::make($context->dot((string) $field['key']))
            ->numeric();

        $constraints = (array) ($field['constraints'] ?? []);

        if (isset($constraints['min']) && is_numeric($constraints['min'])) {
            $component->minValue((float) $constraints['min']);
        }

        if (isset($constraints['max']) && is_numeric($constraints['max'])) {
            $component->maxValue((float) $constraints['max']);
        }

        return $this->applyAffixes(
            $this->applyCommon($component, $field, $context),
            $field,
        );
    }
}
