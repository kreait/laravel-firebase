<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase;

use Illuminate\Contracts\Container\Container;
use Kreait\Firebase;
use Laravel\Lumen\Application as Lumen;

final class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        // @codeCoverageIgnoreStart
        if (!$this->app->runningInConsole()) {
            return;
        }

        if ($this->app instanceof Lumen) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $this->publishes([
            __DIR__.'/../config/firebase.php' => $this->app->configPath('firebase.php'),
        ], 'config');
    }

    public function register()
    {
        // @codeCoverageIgnoreStart
        if ($this->app instanceof Lumen) {
            $this->app->configure('firebase');
        }
        // @codeCoverageIgnoreEnd

        $this->mergeConfigFrom(__DIR__.'/../config/firebase.php', 'firebase');

        $this->registerManager();
        $this->registerComponents();
    }

    private function registerComponents(): void
    {
        $this->app->singleton(Firebase\Contract\Auth::class, static function (Container $app) {
            return $app->make(FirebaseProjectManager::class)->project()->auth();
        });
        $this->app->alias(Firebase\Contract\Auth::class, Firebase\Auth::class);
        $this->app->alias(Firebase\Contract\Auth::class, 'firebase.auth');

        $this->app->singleton(Firebase\Database::class, static function (Container $app) {
            return $app->make(FirebaseProjectManager::class)->project()->database();
        });
        $this->app->alias(Firebase\Database::class, 'firebase.database');

        $this->app->singleton(Firebase\DynamicLinks::class, static function (Container $app) {
            return $app->make(FirebaseProjectManager::class)->project()->dynamicLinks();
        });
        $this->app->alias(Firebase\DynamicLinks::class, 'firebase.dynamic_links');

        $this->app->singleton(Firebase\Firestore::class, static function (Container $app) {
            return $app->make(FirebaseProjectManager::class)->project()->firestore();
        });
        $this->app->alias(Firebase\Firestore::class, 'firebase.firestore');

        $this->app->singleton(Firebase\Messaging::class, static function (Container $app) {
            return $app->make(FirebaseProjectManager::class)->project()->messaging();
        });
        $this->app->alias(Firebase\Messaging::class, 'firebase.messaging');

        $this->app->singleton(Firebase\RemoteConfig::class, static function (Container $app) {
            return $app->make(FirebaseProjectManager::class)->project()->remoteConfig();
        });
        $this->app->alias(Firebase\RemoteConfig::class, 'firebase.remote_config');

        $this->app->singleton(Firebase\Storage::class, static function (Container $app) {
            return $app->make(FirebaseProjectManager::class)->project()->storage();
        });
        $this->app->alias(Firebase\Storage::class, 'firebase.storage');
    }

    private function registerManager(): void
    {
        $this->app->singleton(FirebaseProjectManager::class, static function (Container $app) {
            return new FirebaseProjectManager($app);
        });
        $this->app->alias(FirebaseProjectManager::class, 'firebase.manager');
    }
}
