<?php

declare(strict_types=1);

use FormSchema\Filament\Validation\LaravelValidationRuleMapper;

function validationSchemaFixture(): array
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
                                    'key' => 'email',
                                    'type' => 'email',
                                    'required' => true,
                                    'constraints' => [
                                        'max_length' => 150,
                                    ],
                                    'validations' => [
                                        ['rule' => 'email', 'params' => []],
                                    ],
                                ],
                                [
                                    'key' => 'amount',
                                    'type' => 'number',
                                    'constraints' => [
                                        'min' => 5,
                                        'max' => 50,
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

test('maps schema to laravel validation rules', function (): void {
    $mapper = app(LaravelValidationRuleMapper::class);

    $rules = $mapper->map(validationSchemaFixture(), 'data');

    expect($rules)->toHaveKey('data.email')
        ->and($rules)->toHaveKey('data.amount')
        ->and($rules['data.email'])->toContain('required')
        ->and($rules['data.email'])->toContain('email')
        ->and($rules['data.amount'])->toContain('nullable')
        ->and($rules['data.amount'])->toContain('numeric');
});
