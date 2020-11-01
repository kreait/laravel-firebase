<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Kreait\Laravel\Firebase\FirebaseProject project(string $name = null)
 * @method static string getDefaultProject()
 * @method static void setDefaultProject(string $name)
 * @method static \Kreait\Firebase\Auth auth()
 * @method static \Kreait\Firebase\Database database()
 * @method static \Kreait\Firebase\DynamicLinks dynamicLinks()
 * @method static \Kreait\Firebase\Firestore firestore()
 * @method static \Kreait\Firebase\Messaging messaging()
 * @method static \Kreait\Firebase\RemoteConfig remoteConfig()
 * @method static \Kreait\Firebase\Storage storage()
 *
 * @see \Kreait\Laravel\Firebase\FirebaseProjectManager
 * @see \Kreait\Laravel\Firebase\FirebaseProject
 */
final class Firebase extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'firebase.manager';
    }
}
