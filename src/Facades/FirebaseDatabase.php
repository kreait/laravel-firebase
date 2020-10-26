<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kreait\Firebase\Database
 * @deprecated 6.0
 */
final class FirebaseDatabase extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'firebase.database';
    }
}
