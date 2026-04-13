<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering\FieldRenderers;

use Filament\Forms\Components\Hidden;
use FormSchema\Filament\Contracts\FieldRenderer;
use Filament\Schemas\Components\Component;
use FormSchema\Filament\Rendering\RendererContext;

class HiddenFieldRenderer implements FieldRenderer
{
    /**
     * @param  array<string, mixed>  $field
     */
    public function render(array $field, RendererContext $context): Component
    {
        return Hidden::make($context->dot((string) $field['key']))
            ->default($field['default'] ?? null)
            ->dehydrated(true);
    }
}
