<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Http\HttpClientOptions;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\Psr16Adapter;

class FirebaseProjectManager
{
    /** @var Application */
    protected $app;

    /** @var FirebaseProject[] */
    protected array $projects = [];

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
        $config = $this->app->config->get('firebase.projects.' . $name);

        if (!$config) {
            throw new InvalidArgumentException("Firebase project [{$name}] not configured.");
        }

        return $config;
    }

    protected function resolveCredentials(string $credentials): string
    {
        $isJsonString = \str_starts_with($credentials, '{');
        $isAbsoluteLinuxPath = \str_starts_with($credentials, '/');
        $isAbsoluteWindowsPath = \str_contains($credentials, ':\\');

        $isRelativePath = !$isJsonString && !$isAbsoluteLinuxPath && !$isAbsoluteWindowsPath;

        return $isRelativePath ? $this->app->basePath($credentials) : $credentials;
    }

    protected function configure(string $name): FirebaseProject
    {
        $factory = new Factory();

        $config = $this->configuration($name);

        if ($tenantId = Arr::get($config, 'auth.tenant_id')) {
            $factory = $factory->withTenantId($tenantId);
        }

        if ($credentials = Arr::get($config, 'credentials.file', Arr::get($config, 'credentials'))) {
            if (is_string($credentials)) {
                $factory = $factory->withServiceAccount($this->resolveCredentials($credentials));
            }

            if (is_array($credentials) && Arr::has($credentials, ['type', 'project_id'])) {
                $factory = $factory->withServiceAccount($credentials);
            }
        }

        if ($databaseUrl = Arr::get($config, 'database.url')) {
            $factory = $factory->withDatabaseUri($databaseUrl);
        }

        if ($authVariableOverride = Arr::get($config, 'database.auth_variable_override')) {
            $factory = $factory->withDatabaseAuthVariableOverride($authVariableOverride);
        }

        if ($defaultStorageBucket = Arr::get($config, 'storage.default_bucket')) {
            $factory = $factory->withDefaultStorageBucket($defaultStorageBucket);
        }

        if ($cacheStore = Arr::get($config, 'cache_store')) {
            $cache = $this->app->make('cache')->store($cacheStore);

            if ($cache instanceof CacheInterface) {
                $cache = new Psr16Adapter($cache);
            } else {
                throw new InvalidArgumentException('The cache store must be an instance of a PSR-6 or PSR-16 cache');
            }

            $factory = $factory->withVerifierCache($cache)->withAuthTokenCache($cache);
        }

        if ($logChannel = Arr::get($config, 'logging.http_log_channel')) {
            $factory = $factory->withHttpLogger(
                $this->app->make('log')->channel($logChannel)
            );
        }

        if ($logChannel = Arr::get($config, 'logging.http_debug_log_channel')) {
            $factory = $factory->withHttpDebugLogger(
                $this->app->make('log')->channel($logChannel)
            );
        }

        $options = HttpClientOptions::default();

        if ($proxy = Arr::get($config, 'http_client_options.proxy')) {
            $options = $options->withProxy($proxy);
        }

        if ($timeout = Arr::get($config, 'http_client_options.timeout')) {
            $options = $options->withTimeOut((float) $timeout);
        }

        if ($middlewares = Arr::get($config, 'http_client_options.guzzle_middlewares')) {
            $options = $options->withGuzzleMiddlewares($middlewares);
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
