<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kreait\Firebase\Storage
 * @deprecated 3.0
 */
final class FirebaseStorage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'firebase.storage';
    }
}
