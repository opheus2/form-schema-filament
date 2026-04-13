<?php

declare(strict_types=1);

use Filament\Schemas\Components\Wizard;
use FormSchema\Filament\Rendering\FilamentSchemaRenderer;

function schemaFixture(int $pages = 1): array
{
    $list = [];

    for ($i = 1; $i <= $pages; $i++) {
        $list[] = [
            'key' => 'page_' . $i,
            'label' => 'Page ' . $i,
            'sections' => [
                [
                    'key' => 'section_' . $i,
                    'label' => 'Section ' . $i,
                    'fields' => [
                        [
                            'key' => 'name_' . $i,
                            'type' => 'short-text',
                            'label' => 'Name',
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    return [
        'version' => '1.0',
        'form' => [
            'name' => 'Test',
            'key' => 'test',
            'pages' => $list,
        ],
    ];
}

test('renders non-wizard components for single page schema', function (): void {
    $renderer = app(FilamentSchemaRenderer::class);

    $components = $renderer->render(schemaFixture());

    expect($components)->toHaveCount(1)
        ->and($components[0])->not->toBeInstanceOf(Wizard::class);
});

test('renders wizard for multi-page schema', function (): void {
    $renderer = app(FilamentSchemaRenderer::class);

    $components = $renderer->render(schemaFixture(2));

    expect($components)->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(Wizard::class);
});
