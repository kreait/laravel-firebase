<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase;

use Illuminate\Contracts\Container\Container;
use Kreait\Firebase;

final class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(): void
    {
        // @codeCoverageIgnoreStart
        if (! $this->app->runningInConsole()) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $this->publishes([
            __DIR__.'/../config/firebase.php' => $this->app->configPath('firebase.php'),
        ], 'config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/firebase.php', 'firebase');

        $this->registerManager();
        $this->registerComponents();
    }

    private function registerComponents(): void
    {
        $this->app->singleton(Firebase\Contract\AppCheck::class, static fn (Container $app) => $app->make(FirebaseProjectManager::class)->project()->appCheck());
        $this->app->alias(Firebase\Contract\AppCheck::class, 'firebase.app_check');

        $this->app->singleton(Firebase\Contract\Auth::class, static fn (Container $app) => $app->make(FirebaseProjectManager::class)->project()->auth());
        $this->app->alias(Firebase\Contract\Auth::class, 'firebase.auth');

        $this->app->singleton(Firebase\Contract\Database::class, static fn (Container $app) => $app->make(FirebaseProjectManager::class)->project()->database());
        $this->app->alias(Firebase\Contract\Database::class, 'firebase.database');

        $this->app->singleton(Firebase\Contract\DynamicLinks::class, static fn (Container $app) => $app->make(FirebaseProjectManager::class)->project()->dynamicLinks());
        $this->app->alias(Firebase\Contract\DynamicLinks::class, 'firebase.dynamic_links');

        $this->app->singleton(Firebase\Contract\Firestore::class, static fn (Container $app) => $app->make(FirebaseProjectManager::class)->project()->firestore());
        $this->app->alias(Firebase\Contract\Firestore::class, 'firebase.firestore');

        $this->app->singleton(Firebase\Contract\Messaging::class, static fn (Container $app) => $app->make(FirebaseProjectManager::class)->project()->messaging());
        $this->app->alias(Firebase\Contract\Messaging::class, 'firebase.messaging');

        $this->app->singleton(Firebase\Contract\RemoteConfig::class, static fn (Container $app) => $app->make(FirebaseProjectManager::class)->project()->remoteConfig());
        $this->app->alias(Firebase\Contract\RemoteConfig::class, 'firebase.remote_config');

        $this->app->singleton(Firebase\Contract\Storage::class, static fn (Container $app) => $app->make(FirebaseProjectManager::class)->project()->storage());
        $this->app->alias(Firebase\Contract\Storage::class, 'firebase.storage');
    }

    private function registerManager(): void
    {
        $this->app->singleton(FirebaseProjectManager::class, static fn (Container $app) => new FirebaseProjectManager($app));
        $this->app->alias(FirebaseProjectManager::class, 'firebase.manager');
    }
}
