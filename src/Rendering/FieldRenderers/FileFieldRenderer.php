<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering\FieldRenderers;

use Filament\Forms\Components\FileUpload;
use FormSchema\Filament\Contracts\FieldRenderer;
use Filament\Schemas\Components\Component;
use FormSchema\Filament\Rendering\RendererContext;

class FileFieldRenderer implements FieldRenderer
{
    use AppliesCommonAttributes;

    /**
     * @param  array<string, mixed>  $field
     */
    public function render(array $field, RendererContext $context): Component
    {
        $key = (string) ($field['key'] ?? 'file');
        $constraints = (array) ($field['constraints'] ?? []);

        $component = FileUpload::make($context->dot($key))
            ->multiple((bool) ($constraints['allow_multiple'] ?? false));

        if (isset($constraints['accept']) && is_array($constraints['accept']) && $constraints['accept'] !== []) {
            $component->acceptedFileTypes(array_values($constraints['accept']));
        }

        return $this->applyCommon($component, $field, $context);
    }
}
