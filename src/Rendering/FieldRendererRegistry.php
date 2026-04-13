<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering;

use InvalidArgumentException;
use FormSchema\Filament\Contracts\FieldRenderer;
use FormSchema\Filament\Rendering\FieldRenderers\TextFieldRenderer;
use FormSchema\Filament\Rendering\FieldRenderers\FileFieldRenderer;
use FormSchema\Filament\Rendering\FieldRenderers\TagFieldRenderer;
use FormSchema\Filament\Rendering\FieldRenderers\DateFieldRenderer;
use FormSchema\Filament\Rendering\FieldRenderers\LayoutFieldRenderer;
use FormSchema\Filament\Rendering\FieldRenderers\EmailFieldRenderer;
use FormSchema\Filament\Rendering\FieldRenderers\PhoneFieldRenderer;
use FormSchema\Filament\Rendering\FieldRenderers\HiddenFieldRenderer;
use FormSchema\Filament\Rendering\FieldRenderers\NumberFieldRenderer;
use FormSchema\Filament\Rendering\FieldRenderers\RatingFieldRenderer;
use FormSchema\Filament\Rendering\FieldRenderers\OptionsFieldRenderer;
use FormSchema\Filament\Rendering\FieldRenderers\BooleanFieldRenderer;
use FormSchema\Filament\Rendering\FieldRenderers\AddressFieldRenderer;
use FormSchema\Filament\Rendering\FieldRenderers\CountryFieldRenderer;

class FieldRendererRegistry
{
    /**
     * @var array<string, class-string<FieldRenderer>|FieldRenderer>
     */
    private array $renderers = [];

    public static function default(): self
    {
        $registry = new self();

        $registry->register('text', TextFieldRenderer::class);
        $registry->register('short-text', TextFieldRenderer::class);
        $registry->register('medium-text', TextFieldRenderer::class);
        $registry->register('long-text', TextFieldRenderer::class);

        $registry->register('number', NumberFieldRenderer::class);
        $registry->register('rating', RatingFieldRenderer::class);
        $registry->register('boolean', BooleanFieldRenderer::class);

        $registry->register('date', DateFieldRenderer::class);
        $registry->register('time', DateFieldRenderer::class);
        $registry->register('datetime', DateFieldRenderer::class);

        $registry->register('email', EmailFieldRenderer::class);
        $registry->register('phone', PhoneFieldRenderer::class);
        $registry->register('url', TextFieldRenderer::class);

        $registry->register('options', OptionsFieldRenderer::class);
        $registry->register('tag', TagFieldRenderer::class);
        $registry->register('country', CountryFieldRenderer::class);
        $registry->register('address', AddressFieldRenderer::class);

        $registry->register('file', FileFieldRenderer::class);
        $registry->register('image', FileFieldRenderer::class);
        $registry->register('video', FileFieldRenderer::class);
        $registry->register('document', FileFieldRenderer::class);

        $registry->register('hidden', HiddenFieldRenderer::class);
        $registry->register('divider', LayoutFieldRenderer::class);
        $registry->register('spacing', LayoutFieldRenderer::class);
        $registry->register('banner', LayoutFieldRenderer::class);

        return $registry;
    }

    public function register(string $type, FieldRenderer|string $renderer): void
    {
        $this->renderers[$type] = $renderer;
    }

    public function has(string $type): bool
    {
        return isset($this->renderers[$type]);
    }

    public function get(string $type): FieldRenderer
    {
        $renderer = $this->renderers[$type] ?? null;

        if ($renderer instanceof FieldRenderer) {
            return $renderer;
        }

        if (is_string($renderer) && class_exists($renderer)) {
            $instance = app($renderer);

            if ($instance instanceof FieldRenderer) {
                return $instance;
            }
        }

        throw new InvalidArgumentException("No field renderer registered for type [{$type}].");
    }
}
