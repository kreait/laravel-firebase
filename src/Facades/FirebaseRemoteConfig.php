<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kreait\Firebase\RemoteConfig
 */
final class FirebaseRemoteConfig extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'firebase.remote_config';
    }
}
