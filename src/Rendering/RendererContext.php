<?php

declare(strict_types=1);

namespace FormSchema\Filament\Rendering;

use FormSchema\Filament\Schema\SchemaInput;
use FormSchema\Filament\Conditions\ConditionEngine;

class RendererContext
{
    public function __construct(
        public readonly SchemaInput $schema,
        public readonly string $statePath,
        public readonly ConditionEngine $conditionEngine,
    ) {}

    public function dot(string $fieldKey): string
    {
        return $this->statePath . '.' . $fieldKey;
    }
}
