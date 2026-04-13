<?php

declare(strict_types=1);

use FormSchema\Filament\Schema\SchemaLoader;
use FormSchema\Filament\Conditions\ConditionEngine;

function conditionalSchemaFixture(): array
{
    return [
        'version' => '1.0',
        'form' => [
            'pages' => [
                [
                    'key' => 'page_1',
                    'sections' => [
                        [
                            'key' => 'section_1',
                            'fields' => [
                                [
                                    'key' => 'transfer_type',
                                    'type' => 'options',
                                    'option_properties' => [
                                        'type' => 'tabs',
                                        'data' => [
                                            ['key' => 'domestic', 'value' => 'Domestic'],
                                            ['key' => 'international', 'value' => 'International'],
                                        ],
                                    ],
                                ],
                                [
                                    'key' => 'swift_code',
                                    'type' => 'short-text',
                                    'hidden' => true,
                                    'conditionals' => [
                                        [
                                            'when' => [
                                                'field' => 'transfer_type',
                                                'operator' => 'is',
                                                'value' => 'international',
                                            ],
                                            'then' => [
                                                'action' => 'show',
                                                'targets' => [
                                                    ['type' => 'field', 'key' => 'swift_code'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

test('evaluates conditional visibility for field targets', function (): void {
    $loader = app(SchemaLoader::class);
    $engine = app(ConditionEngine::class);

    $input = $loader->fromCanonical(conditionalSchemaFixture());
    $swiftField = $input->fieldIndex['swift_code'];

    $hiddenState = ['transfer_type' => 'domestic'];
    $shownState = ['transfer_type' => 'international'];

    expect($engine->isFieldVisible($swiftField, $input, $hiddenState))->toBeFalse()
        ->and($engine->isFieldVisible($swiftField, $input, $shownState))->toBeTrue();
});
