<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kreait\Firebase\DynamicLinks
 */
final class FirebaseDynamicLinks extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'firebase.dynamic_links';
    }
}
