<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kreait\Firebase\DynamicLinks
 * @deprecated 3.0 Use {@see \Kreait\Laravel\Firebase\Facades\Firebase::dynamicLinks()} instead.
 */
final class FirebaseDynamicLinks extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'firebase.dynamic_links';
    }
}
