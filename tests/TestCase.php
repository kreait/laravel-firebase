<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Tests;

use Kreait\Laravel\Firebase;

/**
 * @internal
 */
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [Firebase\ServiceProvider::class];
    }
}
