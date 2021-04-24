<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kreait\Firebase\Contract\Database
 * @deprecated 3.0 Use {@see \Kreait\Laravel\Firebase\Facades\Firebase::database()} instead.
 */
final class FirebaseDatabase extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'firebase.database';
    }
}
