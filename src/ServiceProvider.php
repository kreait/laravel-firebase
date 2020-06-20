<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase;

use Illuminate\Contracts\Container\Container;
use Laravel\Lumen\Application as Lumen;
use Kreait\Firebase;

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
            __DIR__ . '/../config/firebase.php' => $this->app->configPath('firebase.php'),
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

        $this->registerFactory();
        $this->registerComponents();
    }

    private function registerComponents(): void
    {
        $this->app->singleton(Firebase\Auth::class, static function (Container $app) {
            return $app->make(Firebase\Factory::class)->createAuth();
        });
        $this->app->alias(Firebase\Auth::class, 'firebase.auth');

        $this->app->singleton(Firebase\Database::class, static function (Container $app) {
            return $app->make(Firebase\Factory::class)->createDatabase();
        });
        $this->app->alias(Firebase\Database::class, 'firebase.database');

        $this->app->singleton(Firebase\DynamicLinks::class, static function (Container $app) {
            $defaultDynamicLinksDomain = $app->make('config')['firebase']['dynamic_links']['default_domain'] ?? null;

            return $app->make(Firebase\Factory::class)->createDynamicLinksService($defaultDynamicLinksDomain);
        });
        $this->app->alias(Firebase\DynamicLinks::class, 'firebase.dynamic_links');

        $this->app->singleton(Firebase\Firestore::class, static function (Container $app) {
            return $app->make(Firebase\Factory::class)->createFirestore();
        });
        $this->app->alias(Firebase\Firestore::class, 'firebase.firestore');

        $this->app->singleton(Firebase\Messaging::class, static function (Container $app) {
            return $app->make(Firebase\Factory::class)->createMessaging();
        });
        $this->app->alias(Firebase\Messaging::class, 'firebase.messaging');

        $this->app->singleton(Firebase\RemoteConfig::class, static function (Container $app) {
            return $app->make(Firebase\Factory::class)->createRemoteConfig();
        });
        $this->app->alias(Firebase\RemoteConfig::class, 'firebase.remote_config');

        $this->app->singleton(Firebase\Storage::class, static function (Container $app) {
            return $app->make(Firebase\Factory::class)->createStorage();
        });
        $this->app->alias(Firebase\Storage::class, 'firebase.storage');
    }

    private function registerFactory(): void
    {
        $this->app->singleton(Firebase\Factory::class, function (Container $app) {
            $factory = new Firebase\Factory();

            $config = $app->make('config')['firebase'];

            if ($credentials = $config['credentials']['file'] ?? null) {
                $resolvedCredentials = $this->resolveCredentials((string) $credentials);

                $factory = $factory->withServiceAccount($resolvedCredentials);
            }

            $enableAutoDiscovery = $config['credentials']['auto_discovery'] ?? true;
            if (!$enableAutoDiscovery) {
                $factory = $factory->withDisabledAutoDiscovery();
            }

            if ($databaseUrl = $config['database']['url'] ?? null) {
                $factory = $factory->withDatabaseUri($databaseUrl);
            }

            if ($defaultStorageBucket = $config['storage']['default_bucket'] ?? null) {
                $factory = $factory->withDefaultStorageBucket($defaultStorageBucket);
            }

            if ($config['debug'] ?? false) {
                $factory = $factory->withEnabledDebug();
            }

            if ($cacheStore = $config['cache_store'] ?? null) {
                $factory = $factory->withVerifierCache(
                    $app->make('cache')->store($cacheStore)
                );
            }

            if ($logChannel = $config['logging']['http_log_channel'] ?? null) {
                $factory = $factory->withHttpLogger(
                    $app->make('log')->channel($logChannel)
                );
            }

            if ($logChannel = $config['logging']['http_debug_log_channel'] ?? null) {
                $factory = $factory->withHttpDebugLogger(
                    $app->make('log')->channel($logChannel)
                );
            }

            return $factory;
        });
    }

    private function resolveCredentials(string $credentials): string
    {
        $isJsonString = strpos($credentials, '{') === 0;
        $isAbsoluteLinuxPath = strpos($credentials, '/') === 0;
        $isAbsoluteWindowsPath = strpos($credentials, ':\\') !== false;

        $isRelativePath = !$isJsonString && !$isAbsoluteLinuxPath && !$isAbsoluteWindowsPath;

        return $isRelativePath ? $this->app->basePath($credentials) : $credentials;
    }
}
