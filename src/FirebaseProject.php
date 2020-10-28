<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase;

use Kreait\Firebase;

class FirebaseProject
{
    /** @var \Kreait\Firebase\Factory $factory */
    protected $factory;

    /** @var array $config */
    protected $config;

    /** @var \Kreait\Firebase\Auth|null $auth */
    protected $auth;

    /** @var \Kreait\Firebase\Database|null $database */
    protected $database;

    /** @var \Kreait\Firebase\DynamicLinks|null $dynamicLinks */
    protected $dynamicLinks;

    /** @var \Kreait\Firebase\Firestore|null $firestore */
    protected $firestore;

    /** @var \Kreait\Firebase\Messaging|null $messaging */
    protected $messaging;

    /** @var \Kreait\Firebase\RemoteConfig|null $remoteConfig */
    protected $remoteConfig;

    /** @var \Kreait\Firebase\Storage|null $storage */
    protected $storage;

    public function __construct(Firebase\Factory $factory, array $config)
    {
        $this->factory = $factory;
        $this->config = $config;
    }

    public function auth(): Firebase\Auth
    {
        if (! $this->auth) {
            $this->auth = $this->factory->createAuth();
        }

        return $this->auth;
    }

    public function database(): Firebase\Database
    {
        if (! $this->database) {
            $this->database = $this->factory->createDatabase();
        }

        return $this->database;
    }

    public function dynamicLinks(): Firebase\DynamicLinks
    {
        if (! $this->dynamicLinks) {
            $this->dynamicLinks = $this->factory->createDynamicLinksService($this->config['dynamic_links']['default_domain'] ?? null);
        }

        return $this->dynamicLinks;
    }

    public function firestore(): Firebase\Firestore
    {
        if (! $this->firestore) {
            $this->firestore = $this->factory->createFirestore();
        }

        return $this->firestore; // @codeCoverageIgnore
    }

    public function messaging(): Firebase\Messaging
    {
        if (! $this->messaging) {
            $this->messaging = $this->factory->createMessaging();
        }

        return $this->messaging;
    }

    public function remoteConfig(): Firebase\RemoteConfig
    {
        if (! $this->remoteConfig) {
            $this->remoteConfig = $this->factory->createRemoteConfig();
        }

        return $this->remoteConfig;
    }

    public function storage(): Firebase\Storage
    {
        if (! $this->storage) {
            $this->storage = $this->factory->createStorage();
        }

        return $this->storage;
    }
}