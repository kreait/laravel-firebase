<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kreait\Firebase\Messaging
 * @deprecated 6.0
 */
final class FirebaseMessaging extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'firebase.messaging';
    }
}
