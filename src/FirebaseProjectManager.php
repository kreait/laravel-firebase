<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase;

use Illuminate\Contracts\Container\Container;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Http\HttpClientOptions;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Component\Cache\Psr16Cache;

class FirebaseProjectManager
{
    /** @var \Illuminate\Contracts\Foundation\Application */
    protected $app;

    /** @var FirebaseProject[] */
    protected $projects = [];

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    public function project(?string $name = null): FirebaseProject
    {
        $name = $name ?? $this->getDefaultProject();

        if (!isset($this->projects[$name])) {
            $this->projects[$name] = $this->configure($name);
        }

        return $this->projects[$name];
    }

    protected function configuration(string $name): array
    {
        $config = $this->app->config->get('firebase.projects.'.$name) ?? null;

        if (!$config) {
            throw new InvalidArgumentException("Firebase project [{$name}] not configured.");
        }

        return $config;
    }

    protected function resolveCredentials(string $credentials): string
    {
        $isJsonString = \strpos($credentials, '{') === 0;
        $isAbsoluteLinuxPath = \strpos($credentials, '/') === 0;
        $isAbsoluteWindowsPath = \strpos($credentials, ':\\') !== false;

        $isRelativePath = !$isJsonString && !$isAbsoluteLinuxPath && !$isAbsoluteWindowsPath;

        return $isRelativePath ? $this->app->basePath($credentials) : $credentials;
    }

    protected function configure(string $name): FirebaseProject
    {
        $factory = new Factory();

        $config = $this->configuration($name);

        if ($tenantId = $config['auth']['tenant_id'] ?? null) {
            $factory = $factory->withTenantId($tenantId);
        }

        if ($credentials = $config['credentials']['file'] ?? null) {
            $resolvedCredentials = $this->resolveCredentials((string) $credentials);

            $factory = $factory->withServiceAccount($resolvedCredentials);
        }

        $enableAutoDiscovery = $config['credentials']['auto_discovery'] ?? ($this->getDefaultProject() == $name ? true : false);
        if (!$enableAutoDiscovery) {
            $factory = $factory->withDisabledAutoDiscovery();
        }

        if ($databaseUrl = $config['database']['url'] ?? null) {
            $factory = $factory->withDatabaseUri($databaseUrl);
        }

        if ($authVariableOverride = $config['database']['auth_variable_override'] ?? null) {
            $factory = $factory->withDatabaseAuthVariableOverride($authVariableOverride);
        }

        if ($defaultStorageBucket = $config['storage']['default_bucket'] ?? null) {
            $factory = $factory->withDefaultStorageBucket($defaultStorageBucket);
        }

        if ($config['debug'] ?? false) {
            $factory = $factory->withEnabledDebug();
        }

        if ($cacheStore = $config['cache_store'] ?? null) {
            $cache = $this->app->make('cache')->store($cacheStore);

            if ($cache instanceof CacheItemPoolInterface) {
                $psr6Cache = $cache;
                $psr16Cache = new Psr16Cache($cache);
            } elseif ($cache instanceof CacheInterface) {
                $psr6Cache = new Psr16Adapter($cache);
                $psr16Cache = $cache;
            } else {
                throw new InvalidArgumentException("The cache store must be an instance of a PSR-6 or PSR-16 cache");
            }

            $factory = $factory
                ->withVerifierCache($psr16Cache)
                ->withAuthTokenCache($psr6Cache);
        }

        if ($logChannel = $config['logging']['http_log_channel'] ?? null) {
            $factory = $factory->withHttpLogger(
                $this->app->make('log')->channel($logChannel)
            );
        }

        if ($logChannel = $config['logging']['http_debug_log_channel'] ?? null) {
            $factory = $factory->withHttpDebugLogger(
                $this->app->make('log')->channel($logChannel)
            );
        }

        $options = HttpClientOptions::default();

        if ($proxy = $config['http_client_options']['proxy'] ?? null) {
            $options = $options->withProxy($proxy);
        }

        if ($timeout = $config['http_client_options']['timeout'] ?? null) {
            $options = $options->withTimeOut((float) $timeout);
        }

        $factory = $factory->withHttpClientOptions($options);

        return new FirebaseProject($factory, $config);
    }

    public function getDefaultProject(): string
    {
        return $this->app->config->get('firebase.default');
    }

    public function setDefaultProject(string $name): void
    {
        $this->app->config->set('firebase.default', $name);
    }

    public function __call($method, $parameters)
    {
        // Pass call to default project
        return $this->project()->{$method}(...$parameters);
    }
}
