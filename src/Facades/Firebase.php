<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kreait\Laravel\Firebase\ProjectsManager
 */
final class Firebase extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'firebase.manager';
    }
}
