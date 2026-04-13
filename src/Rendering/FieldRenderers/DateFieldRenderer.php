<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering\FieldRenderers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Component;
use Filament\Forms\Components\DateTimePicker;
use FormSchema\Filament\Contracts\FieldRenderer;
use FormSchema\Filament\Rendering\RendererContext;

class DateFieldRenderer implements FieldRenderer
{
    use AppliesCommonAttributes;

    /**
     * @param  array<string, mixed>  $field
     */
    public function render(array $field, RendererContext $context): Component
    {
        $type = (string) ($field['type'] ?? 'date');
        $key = (string) ($field['key'] ?? '');

        $component = match ($type) {
            'time' => TimePicker::make($context->dot($key)),
            'datetime' => DateTimePicker::make($context->dot($key)),
            default => DatePicker::make($context->dot($key)),
        };

        return $this->applyCommon($component, $field, $context);
    }
}
