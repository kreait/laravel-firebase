<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Kreait\Laravel\Firebase\FirebaseProject project(string $name = null)
 * @method static string getDefaultProject()
 * @method static void setDefaultProject(string $name)
 * @method static \Kreait\Firebase\Contract\AppCheck appCheck()
 * @method static \Kreait\Firebase\Contract\Auth auth()
 * @method static \Kreait\Firebase\Contract\Database database()
 * @method static \Kreait\Firebase\Contract\DynamicLinks dynamicLinks()
 * @method static \Kreait\Firebase\Contract\Firestore firestore()
 * @method static \Kreait\Firebase\Contract\Messaging messaging()
 * @method static \Kreait\Firebase\Contract\RemoteConfig remoteConfig()
 * @method static \Kreait\Firebase\Contract\Storage storage()
 *
 * @see \Kreait\Laravel\Firebase\FirebaseProjectManager
 * @see \Kreait\Laravel\Firebase\FirebaseProject
 */
final class Firebase extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'firebase.manager';
    }
}
