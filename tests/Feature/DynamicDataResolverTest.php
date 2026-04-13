<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use FormSchema\Filament\Support\HttpDynamicDataResolver;

test('http dynamic resolver resolves dynamic options from source config', function (): void {
    Http::fake([
        'https://example.test/options*' => Http::response([
            'data' => [
                ['code' => 'bank_1', 'name' => 'First Bank'],
                ['code' => 'bank_2', 'name' => 'Second Bank'],
            ],
        ]),
    ]);

    $resolver = new HttpDynamicDataResolver();

    $field = [
        'key' => 'bank',
        'option_properties' => [
            'source' => [
                'enabled' => true,
                'endpoint' => 'https://example.test/options',
                'method' => 'GET',
                'params' => [
                    ['key' => 'country', 'value' => '{field:country}'],
                ],
                'items_path' => 'data',
                'key_path' => 'name',
                'value_path' => 'code',
            ],
        ],
    ];

    $options = $resolver->resolveDynamicOptions($field, [], ['country' => 'NG']);

    expect($options)->toBe([
        'bank_1' => 'First Bank',
        'bank_2' => 'Second Bank',
    ]);
});

test('http dynamic resolver resolves dynamic options for object map responses with wildcard key path', function (): void {
    Http::fake([
        'https://example.test/options-map*' => Http::response([
            'data' => [
                '044' => 'Access Bank',
                '050' => 'Ecobank',
            ],
        ]),
    ]);

    $resolver = new HttpDynamicDataResolver();

    $field = [
        'key' => 'bank_id',
        'option_properties' => [
            'source' => [
                'enabled' => true,
                'endpoint' => 'https://example.test/options-map',
                'method' => 'GET',
                'items_path' => 'data',
                'key_path' => '*',
                'value_path' => '*',
            ],
        ],
    ];

    $options = $resolver->resolveDynamicOptions($field, [], []);

    expect($options)->toBe([
        '044' => 'Access Bank',
        '050' => 'Ecobank',
    ]);
});

test('http dynamic resolver supports relative endpoints', function (): void {
    config()->set('app.url', 'https://app.example.test');
    URL::forceRootUrl('https://app.example.test');

    Http::fake([
        'https://app.example.test/wallets/recipients/available-banks*' => Http::response([
            'data' => [
                ['code' => '044', 'name' => 'Access Bank'],
            ],
        ]),
    ]);

    $resolver = new HttpDynamicDataResolver();

    $field = [
        'key' => 'bank_id',
        'option_properties' => [
            'source' => [
                'enabled' => true,
                'endpoint' => '/wallets/recipients/available-banks',
                'method' => 'GET',
                'params' => [
                    ['key' => 'country', 'value' => 'NG'],
                ],
                'items_path' => 'data',
                'key_path' => 'name',
                'value_path' => 'code',
            ],
        ],
    ];

    $options = $resolver->resolveDynamicOptions($field, [], []);

    expect($options)->toBe([
        '044' => 'Access Bank',
    ]);
});

test('http dynamic resolver resolves autofill payload when conditions pass', function (): void {
    Http::fake([
        'https://example.test/autofill*' => Http::response([
            'data' => ['account_name' => 'Ada Lovelace'],
        ]),
    ]);

    $resolver = new HttpDynamicDataResolver();

    $field = [
        'key' => 'account_number',
        'autofill' => [
            'enabled' => true,
            'endpoint' => 'https://example.test/autofill',
            'method' => 'GET',
            'params' => [
                ['key' => 'account_number', 'value' => '{field:account_number}'],
            ],
            'when' => [
                'all' => [
                    ['key' => 'account_number', 'operator' => 'length_gte', 'value' => 10],
                ],
            ],
        ],
    ];

    $response = $resolver->resolveAutofill($field, [], ['account_number' => '0123456789'], '0123456789');

    expect($response)->toBe([
        'data' => ['account_name' => 'Ada Lovelace'],
    ]);
});
