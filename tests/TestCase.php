<?php

declare(strict_types=1);

namespace FormSchema\Filament\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use FormSchema\Filament\FormSchemaFilamentServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            FormSchemaFilamentServiceProvider::class,
        ];
    }
}
