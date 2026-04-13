# Form Schema Filament

Reusable Filament plugin/SDK that renders canonical Form Schema v1 payloads into Filament components.

## Features

- Canonical schema rendering (`form.pages.sections.fields`)
- Wizard rendering when schema has more than one page
- Stable state paths based on field keys
- Field renderer registry for extension/customization
- Conditional visibility support (`conditionals` and `visible_if`)
- Validation integration through `form-builder/form-schema-validator`
- Submission payload extraction keyed by schema field keys

## Installation

```bash
composer require form-builder/form-schema-filament
```

## Register Plugin

```php
use FormSchema\Filament\FormSchemaFilamentPlugin;

$panel->plugins([
    FormSchemaFilamentPlugin::make(),
]);
```

## Render Dynamic Form Components

```php
use FormSchema\Filament\Rendering\FilamentSchemaRenderer;

$schema = $formVersion->schema; // canonical schema envelope

$components = app(FilamentSchemaRenderer::class)->render($schema, 'data');
$rules = app(FilamentSchemaRenderer::class)->validationRules($schema, 'data');
```

## Submission Payload

```php
$payload = app(FilamentSchemaRenderer::class)->extractSubmissionPayload(
    $schema,
    $this->form->getState()['data'] ?? [],
);
```

## Validation

```php
use FormSchema\Filament\Validation\SubmissionValidatorBridge;

$result = app(SubmissionValidatorBridge::class)->validate($schema, $payload);

if (! $result->isValid()) {
    // map $result->errors() into ValidationException
}
```

## Extending Field Types

```php
use FormSchema\Filament\FormSchemaFilamentPlugin;

FormSchemaFilamentPlugin::make()
    ->registerFieldRenderer('my-custom-field', App\Support\Filament\MyFieldRenderer::class);
```

## Dynamic Options, Autofill, and Validation Response

The package now supports dynamic runtime fetches for:

- `option_properties.source` (dynamic options)
- `autofill`
- `validation_response`

By default, this uses an HTTP resolver (`Http::get` / `Http::post`) that reads schema `endpoint`, `method`, `headers`, `params`, `when`, and response mapping paths.

If you want full control, provide your own resolver class:

```php
use FormSchema\Filament\FormSchemaFilamentPlugin;
use FormSchema\Filament\Contracts\DynamicDataResolver;

$panel->plugins([
    FormSchemaFilamentPlugin::make()
        ->dynamicDataResolver(App\Support\FormSchema\CustomDynamicDataResolver::class),
]);
```

Your resolver should implement `FormSchema\Filament\Contracts\DynamicDataResolver`.

## Tests

```bash
composer test
```
