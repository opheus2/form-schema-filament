<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering\FieldRenderers;

use Filament\Forms\Components\TagsInput;
use FormSchema\Filament\Contracts\FieldRenderer;
use Filament\Schemas\Components\Component;
use FormSchema\Filament\Rendering\RendererContext;

class TagFieldRenderer implements FieldRenderer
{
    use AppliesCommonAttributes;

    /**
     * @param  array<string, mixed>  $field
     */
    public function render(array $field, RendererContext $context): Component
    {
        $component = TagsInput::make($context->dot((string) $field['key']));

        return $this->applyCommon($component, $field, $context);
    }
}
