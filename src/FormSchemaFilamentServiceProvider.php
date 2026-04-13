<?php

declare(strict_types=1);

namespace FormSchema\Filament;

use Illuminate\Support\ServiceProvider;
use FormSchema\Filament\Schema\SchemaLoader;
use FormSchema\Filament\State\SubmissionPayloadExtractor;
use FormSchema\Filament\Rendering\FilamentSchemaRenderer;
use FormSchema\Filament\Rendering\FieldRendererRegistry;
use FormSchema\Filament\Conditions\ConditionEngine;
use FormSchema\Filament\Validation\SchemaValidatorBridge;
use FormSchema\Filament\Validation\SubmissionValidatorBridge;
use FormSchema\Filament\Validation\LaravelValidationRuleMapper;

class FormSchemaFilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/form-schema-filament.php', 'form-schema-filament');

        $this->app->singleton(FieldRendererRegistry::class, fn (): FieldRendererRegistry => FieldRendererRegistry::default());
        $this->app->singleton(SchemaLoader::class, fn (): SchemaLoader => new SchemaLoader());
        $this->app->singleton(ConditionEngine::class, fn (): ConditionEngine => new ConditionEngine());
        $this->app->singleton(LaravelValidationRuleMapper::class, fn (): LaravelValidationRuleMapper => new LaravelValidationRuleMapper());
        $this->app->singleton(SchemaValidatorBridge::class, fn (): SchemaValidatorBridge => new SchemaValidatorBridge());
        $this->app->singleton(SubmissionValidatorBridge::class, fn (): SubmissionValidatorBridge => new SubmissionValidatorBridge());

        $this->app->singleton(SubmissionPayloadExtractor::class, function (): SubmissionPayloadExtractor {
            /** @var array<int, string> $layoutFields */
            $layoutFields = config('form-schema-filament.ignore_layout_fields_in_payload', ['divider', 'spacing', 'banner']);

            return new SubmissionPayloadExtractor($layoutFields);
        });

        $this->app->singleton(FilamentSchemaRenderer::class, function (): FilamentSchemaRenderer {
            return new FilamentSchemaRenderer(
                loader: $this->app->make(SchemaLoader::class),
                registry: $this->app->make(FieldRendererRegistry::class),
                conditionEngine: $this->app->make(ConditionEngine::class),
                ruleMapper: $this->app->make(LaravelValidationRuleMapper::class),
                payloadExtractor: $this->app->make(SubmissionPayloadExtractor::class),
                failOnUnsupported: (bool) config('form-schema-filament.fail_on_unsupported_fields', true),
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/form-schema-filament.php' => config_path('form-schema-filament.php'),
        ], 'form-schema-filament-config');
    }
}
