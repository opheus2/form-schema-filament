<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering\FieldRenderers;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use FormSchema\Filament\Contracts\FieldRenderer;
use FormSchema\Filament\Rendering\RendererContext;

class PhoneFieldRenderer implements FieldRenderer
{
    use AppliesCommonAttributes;

    /**
     * @param  array<string, mixed>  $field
     */
    public function render(array $field, RendererContext $context): Component
    {
        $component = TextInput::make($context->dot((string) $field['key']))->tel();

        return $this->applyCommon($component, $field, $context);
    }
}
