<?php

declare(strict_types=1);

namespace FormSchema\Filament\Contracts;

use Filament\Schemas\Components\Component;
use FormSchema\Filament\Rendering\RendererContext;

interface FieldRenderer
{
    /**
     * @param  array<string, mixed>  $field
     */
    public function render(array $field, RendererContext $context): Component;
}
