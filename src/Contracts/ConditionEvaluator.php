<?php

declare(strict_types=1);

namespace FormSchema\Filament\Contracts;

interface ConditionEvaluator
{
    /**
     * @param  array<string, mixed>  $condition
     * @param  array<string, mixed>  $state
     */
    public function evaluate(array $condition, array $state): bool;
}
