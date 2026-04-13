<?php

declare(strict_types=1);

namespace FormSchema\Filament;

use Filament\Panel;
use Filament\Contracts\Plugin;
use FormSchema\Filament\Contracts\FieldRenderer;
use FormSchema\Filament\Rendering\FieldRendererRegistry;

class FormSchemaFilamentPlugin implements Plugin
{
    private bool $failOnUnsupportedFields = true;

    /**
     * @var class-string|null
     */
    private ?string $dynamicDataResolver = null;

    public static function make(): static
    {
        return new static();
    }

    public function getId(): string
    {
        return 'form-schema-filament';
    }

    public function register(Panel $panel): void
    {
        config()->set('form-schema-filament.fail_on_unsupported_fields', $this->failOnUnsupportedFields);

        if (is_string($this->dynamicDataResolver) && '' !== $this->dynamicDataResolver) {
            config()->set('form-schema-filament.dynamic_data_resolver', $this->dynamicDataResolver);
        }
    }

    public function boot(Panel $panel): void
    {
        // No panel boot side-effects required.
    }

    public function failOnUnsupportedFields(bool $condition = true): static
    {
        $this->failOnUnsupportedFields = $condition;

        return $this;
    }

    public function registerFieldRenderer(string $type, FieldRenderer|string $renderer): static
    {
        /** @var FieldRendererRegistry $registry */
        $registry = app(FieldRendererRegistry::class);
        $registry->register($type, $renderer);

        return $this;
    }

    /**
     * @param  class-string  $resolver
     */
    public function dynamicDataResolver(string $resolver): static
    {
        $this->dynamicDataResolver = $resolver;

        return $this;
    }
}
