<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase;

use Kreait\Firebase\Contract\AppCheck;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Contract\DynamicLinks;
use Kreait\Firebase\Contract\Firestore;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Contract\RemoteConfig;
use Kreait\Firebase\Contract\Storage;
use Kreait\Firebase\Factory;

class FirebaseProject
{
    protected Factory $factory;

    protected array $config;

    protected ?AppCheck $appCheck = null;

    protected ?Auth $auth = null;

    protected ?Database $database = null;

    protected ?DynamicLinks $dynamicLinks = null;

    protected ?Firestore $firestore = null;

    protected ?Messaging $messaging = null;

    protected ?RemoteConfig $remoteConfig = null;

    protected ?Storage $storage = null;

    public function __construct(Factory $factory, array $config)
    {
        $this->factory = $factory;
        $this->config = $config;
    }

    public function appCheck(): AppCheck
    {
        if (! $this->appCheck) {
            $this->appCheck = $this->factory->createAppCheck();
        }

        return $this->appCheck;
    }

    public function auth(): Auth
    {
        if (! $this->auth) {
            $this->auth = $this->factory->createAuth();
        }

        return $this->auth;
    }

    public function database(): Database
    {
        if (! $this->database) {
            $this->database = $this->factory->createDatabase();
        }

        return $this->database;
    }

    public function dynamicLinks(): DynamicLinks
    {
        if (! $this->dynamicLinks) {
            $this->dynamicLinks = $this->factory->createDynamicLinksService($this->config['dynamic_links']['default_domain'] ?? null);
        }

        return $this->dynamicLinks;
    }

    public function firestore(): Firestore
    {
        if (! $this->firestore) {
            $this->firestore = $this->factory->createFirestore();
        }

        return $this->firestore; // @codeCoverageIgnore
    }

    public function messaging(): Messaging
    {
        if (! $this->messaging) {
            $this->messaging = $this->factory->createMessaging();
        }

        return $this->messaging;
    }

    public function remoteConfig(): RemoteConfig
    {
        if (! $this->remoteConfig) {
            $this->remoteConfig = $this->factory->createRemoteConfig();
        }

        return $this->remoteConfig;
    }

    public function storage(): Storage
    {
        if (! $this->storage) {
            $this->storage = $this->factory->createStorage();
        }

        return $this->storage;
    }
}
