<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase;

use Kreait\Firebase;

class FirebaseProject
{
    /** @var \Kreait\Firebase\Factory */
    protected $factory;

    /** @var array */
    protected $config;

    /** @var \Kreait\Firebase\Contract\Auth|null */
    protected $auth;

    /** @var \Kreait\Firebase\Contract\Database|null */
    protected $database;

    /** @var \Kreait\Firebase\Contract\DynamicLinks|null */
    protected $dynamicLinks;

    /** @var \Kreait\Firebase\Contract\Firestore|null */
    protected $firestore;

    /** @var \Kreait\Firebase\Contract\Messaging|null */
    protected $messaging;

    /** @var \Kreait\Firebase\Contract\RemoteConfig|null */
    protected $remoteConfig;

    /** @var \Kreait\Firebase\Contract\Storage|null */
    protected $storage;

    public function __construct(Firebase\Factory $factory, array $config)
    {
        $this->factory = $factory;
        $this->config = $config;
    }

    public function auth(): Firebase\Contract\Auth
    {
        if (!$this->auth) {
            $this->auth = $this->factory->createAuth();
        }

        return $this->auth;
    }

    public function database(): Firebase\Contract\Database
    {
        if (!$this->database) {
            $this->database = $this->factory->createDatabase();
        }

        return $this->database;
    }

    public function dynamicLinks(): Firebase\Contract\DynamicLinks
    {
        if (!$this->dynamicLinks) {
            $this->dynamicLinks = $this->factory->createDynamicLinksService($this->config['dynamic_links']['default_domain'] ?? null);
        }

        return $this->dynamicLinks;
    }

    public function firestore(): Firebase\Contract\Firestore
    {
        if (!$this->firestore) {
            $this->firestore = $this->factory->createFirestore();
        }

        return $this->firestore; // @codeCoverageIgnore
    }

    public function messaging(): Firebase\Contract\Messaging
    {
        if (!$this->messaging) {
            $this->messaging = $this->factory->createMessaging();
        }

        return $this->messaging;
    }

    public function remoteConfig(): Firebase\Contract\RemoteConfig
    {
        if (!$this->remoteConfig) {
            $this->remoteConfig = $this->factory->createRemoteConfig();
        }

        return $this->remoteConfig;
    }

    public function storage(): Firebase\Contract\Storage
    {
        if (!$this->storage) {
            $this->storage = $this->factory->createStorage();
        }

        return $this->storage;
    }
}
