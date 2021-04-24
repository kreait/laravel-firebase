<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kreait\Firebase\Contract\Firestore
 * @mixin \Kreait\Firebase\Contract\Firestore
 *
 * @deprecated 3.0 Use {@see \Kreait\Laravel\Firebase\Facades\Firebase::firestore()} instead.
 */
final class FirebaseFirestore extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'firebase.firestore';
    }
}
