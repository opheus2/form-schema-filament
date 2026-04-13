<?php

declare(strict_types=1);

use FormSchema\Filament\State\SubmissionPayloadExtractor;

function payloadSchemaFixture(): array
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
                                ['key' => 'name', 'type' => 'short-text'],
                                ['key' => 'secret', 'type' => 'hidden', 'default' => 'internal-token'],
                                ['key' => 'divider_1', 'type' => 'divider'],
                                ['key' => 'spacer_1', 'type' => 'spacing'],
                                ['key' => 'notice_1', 'type' => 'banner'],
                                [
                                    'key' => 'address',
                                    'type' => 'address',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

test('extracts submission payload by field keys and excludes layout fields', function (): void {
    $extractor = app(SubmissionPayloadExtractor::class);

    $payload = $extractor->extract(payloadSchemaFixture(), [
        'name' => 'Ada',
        'address' => ['city' => 'Lagos'],
    ]);

    expect($payload)->toBe([
        'name' => 'Ada',
        'secret' => 'internal-token',
        'address' => ['city' => 'Lagos'],
    ]);
});
