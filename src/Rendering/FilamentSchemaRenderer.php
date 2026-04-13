<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering;

use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Section;
use FormSchema\Filament\Schema\SchemaInput;
use Filament\Schemas\Components\Wizard\Step;
use FormSchema\Filament\Schema\SchemaLoader;
use Filament\Schemas\Components\Utilities\Get;
use FormSchema\Filament\Conditions\ConditionEngine;
use FormSchema\Filament\Contracts\DynamicDataResolver;
use FormSchema\Filament\Contracts\ValidationRuleMapper;
use FormSchema\Filament\State\SubmissionPayloadExtractor;
use FormSchema\Filament\Validation\SchemaValidatorBridge;
use FormSchema\Filament\Exceptions\InvalidSchemaException;
use FormSchema\Filament\Exceptions\UnsupportedFieldTypeException;

class FilamentSchemaRenderer
{
    public function __construct(
        private readonly SchemaLoader $loader,
        private readonly FieldRendererRegistry $registry,
        private readonly ConditionEngine $conditionEngine,
        private readonly DynamicDataResolver $dynamicDataResolver,
        private readonly ValidationRuleMapper $ruleMapper,
        private readonly SubmissionPayloadExtractor $payloadExtractor,
        private readonly bool $failOnUnsupported = true,
    ) {}

    /**
     * @param  array<string, mixed>  $schema
     * @return array<int, Component>
     */
    public function render(array $schema, ?string $statePath = null): array
    {
        $statePath ??= (string) config('form-schema-filament.state_path', 'data');

        $validation = app(SchemaValidatorBridge::class)->validate($schema);

        if ( ! $validation->isValid()) {
            throw InvalidSchemaException::fromErrors($validation->errors());
        }

        $input = $this->loader->fromCanonical($schema);
        $context = new RendererContext($input, $statePath, $this->conditionEngine, $this->dynamicDataResolver);

        if (count($input->pages) > 1) {
            return [$this->buildWizard($input, $context)];
        }

        $page = $input->pages[0];

        return $this->buildPageComponents($page, $context);
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, array<int, string>>
     */
    public function validationRules(array $schema, ?string $statePath = null): array
    {
        $statePath ??= (string) config('form-schema-filament.state_path', 'data');

        return $this->ruleMapper->map($schema, $statePath);
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function extractSubmissionPayload(array $schema, array $state): array
    {
        return $this->payloadExtractor->extract($schema, $state);
    }

    private function buildWizard(SchemaInput $input, RendererContext $context): Wizard
    {
        $steps = [];

        foreach ($input->pages as $page) {
            $pageKey = is_string($page['key'] ?? null) ? $page['key'] : null;

            $step = Step::make((string) ($page['label'] ?? $pageKey ?? 'Page'))
                ->description(is_string($page['description'] ?? null) ? $page['description'] : null)
                ->schema($this->buildPageComponents($page, $context))
                ->visible(fn (Get $get): bool => $this->conditionEngine->isPageVisible(
                    page: $page,
                    schema: $input,
                    state: (array) ($get($context->statePath) ?? []),
                ));

            $steps[] = $step;
        }

        return Wizard::make($steps);
    }

    /**
     * @param  array<string, mixed>  $page
     * @return array<int, Component>
     */
    private function buildPageComponents(array $page, RendererContext $context): array
    {
        $components = [];
        $pageKey = is_string($page['key'] ?? null) ? $page['key'] : null;

        foreach ((array) ($page['sections'] ?? []) as $section) {
            $components[] = $this->buildSection($section, $pageKey, $context);
        }

        return $components;
    }

    /**
     * @param  array<string, mixed>  $section
     */
    private function buildSection(array $section, ?string $pageKey, RendererContext $context): Section
    {
        $sectionComponents = [];

        foreach ((array) ($section['fields'] ?? []) as $field) {
            $type = (string) ($field['type'] ?? '');

            if ( ! $this->registry->has($type)) {
                if ($this->failOnUnsupported) {
                    throw UnsupportedFieldTypeException::make($type);
                }

                continue;
            }

            $renderer = $this->registry->get($type);
            $sectionComponents[] = $renderer->render($field, $context);
        }

        return Section::make((string) ($section['label'] ?? $section['key'] ?? 'Section'))
            ->description(is_string($section['description'] ?? null) ? $section['description'] : null)
            ->components($sectionComponents)
            ->visible(fn (Get $get): bool => $this->conditionEngine->isSectionVisible(
                section: $section,
                schema: $context->schema,
                state: (array) ($get($context->statePath) ?? []),
                pageKey: $pageKey,
            ));
    }
}
