<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering\FieldRenderers;

use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Component;
use FormSchema\Filament\Contracts\FieldRenderer;
use FormSchema\Filament\Rendering\RendererContext;

class LayoutFieldRenderer implements FieldRenderer
{
    /**
     * @param  array<string, mixed>  $field
     */
    public function render(array $field, RendererContext $context): Component
    {
        $type = (string) ($field['type'] ?? 'divider');
        $key = (string) ($field['key'] ?? $type);

        if ('spacing' === $type) {
            $height = (int) (($field['ui']['multiline_rows'] ?? 16));

            return Placeholder::make($context->dot($key) . '_layout')
                ->hiddenLabel()
                ->content(new HtmlString('<div style="height: ' . $height . 'px;"></div>'));
        }

        if ('banner' === $type) {
            $banner = (array) ($field['banner_properties'] ?? []);
            $message = (string) ($banner['message'] ?? ($field['help_text'] ?? ''));
            $isHtml = (bool) ($banner['is_html'] ?? false);
            $content = $isHtml ? $message : e($message);

            return Placeholder::make($context->dot($key) . '_layout')
                ->hiddenLabel()
                ->content(new HtmlString('<div class="rounded-lg border px-4 py-3 text-sm">' . $content . '</div>'));
        }

        return Placeholder::make($context->dot($key) . '_layout')
            ->hiddenLabel()
            ->content(new HtmlString('<hr style="border-top: 1px solid #e5e7eb;" />'));
    }
}
