<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering\FieldRenderers;

use Filament\Forms\Components\Select;
use FormSchema\Filament\Contracts\FieldRenderer;
use Filament\Schemas\Components\Component;
use FormSchema\Filament\Rendering\RendererContext;

class RatingFieldRenderer implements FieldRenderer
{
    use AppliesCommonAttributes;

    /**
     * @param  array<string, mixed>  $field
     */
    public function render(array $field, RendererContext $context): Component
    {
        $max = (int) (($field['constraints']['max'] ?? 5));
        $max = max(1, $max);

        $options = [];
        for ($i = 1; $i <= $max; $i++) {
            $options[$i] = (string) $i;
        }

        $component = Select::make($context->dot((string) $field['key']))
            ->options($options);

        return $this->applyCommon($component, $field, $context);
    }
}
