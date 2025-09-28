<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Tests;

use InvalidArgumentException;
use Kreait\Laravel\Firebase\FirebaseProjectManager;
use PHPUnit\Framework\Attributes\Test;
use TypeError;

/**
 * @internal
 */
final class FirebaseProjectManagerTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('firebase.projects.app.credentials', __DIR__.'/_fixtures/service_account.json');
    }

    private function projectManager(): FirebaseProjectManager
    {
        return app(FirebaseProjectManager::class);
    }

    #[Test]
    public function a_project_configuration_has_to_exist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/undefined.+not configured/');

        $this->projectManager()->project('undefined');
    }

    #[Test]
    public function a_default_project_can_be_set(): void
    {
        $manager = $this->projectManager();

        $manager->setDefaultProject('undefined');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/undefined.+not configured/');
        $manager->project();
    }

    #[Test]
    public function calls_are_passed_to_default_project(): void
    {
        $manager = $this->projectManager();

        $this->assertSame($manager->project()->auth(), $manager->auth());
    }

    #[Test]
    public function credentials_can_be_configured_using_a_json_file(): void
    {
        config(['firebase.projects.app.credentials' => __DIR__.'/_fixtures/broken.json']);

        $this->expectException(InvalidArgumentException::class);
        $this->projectManager()->project();
    }

    #[Test]
    public function json_file_credentials_can_be_used_using_the_deprecated_configuration_entry(): void
    {
        config(['firebase.projects.app.credentials.file' => __DIR__.'/_fixtures/broken.json']);

        $this->expectException(InvalidArgumentException::class);
        $this->projectManager()->project();
    }

    #[Test]
    public function credentials_can_be_configured_using_an_array(): void
    {
        // Set configuration and retrieve project
        config(['firebase.projects.app.credentials' => [
            'type' => 'service_account',
            'project_id' => 'project',
            'private_key_id' => 'private_key_id',
            // 'private_key' => '-----BEGIN PRIVATE KEY-----\nsome gibberish\n-----END PRIVATE KEY-----\n',
            'client_email' => 'client@email.tld',
            'client_id' => '1234567890',
            'auth_uri' => 'https://some.google.tld/o/oauth2/auth',
            'token_uri' => 'https://some.google.tld/o/oauth2/token',
            'auth_provider_x509_cert_url' => 'https://some.google.tld/oauth2/v1/certs',
            'client_x509_cert_url' => 'https://some.google.tld/robot/v1/metadata/x509/user%40project.iam.gserviceaccount.com',
        ]]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/private.*key/i'); // <7.22 private_key, >=7.22 privateKey

        $this->projectManager()->project()->auth();
    }

    #[Test]
    public function the_realtime_database_url_can_be_configured(): void
    {
        $this->app->config->set('firebase.projects.app.database.url', 'invalid');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/unexpected.+invalid/i');

        $this->projectManager()->project()->database();

    }

    #[Test]
    public function the_dynamic_links_default_domain_can_be_configured(): void
    {
        config(['firebase.projects.app.dynamic_links.default_domain' => 'invalid']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/url.+invalid/i');
        $this->projectManager()->project()->dynamicLinks();
    }

    #[Test]
    public function the_storage_default_bucket_can_be_configured(): void
    {
        config(['firebase.projects.app.storage.default_bucket' => 1]);

        $this->expectException(TypeError::class);
        $this->expectExceptionMessageMatches('/string.+int/i');
        $this->projectManager()->project()->storage();
    }

    #[Test]
    public function http_client_proxy_can_be_configured(): void
    {
        config(['firebase.projects.app.http_client_options.proxy' => 1]);

        $this->expectException(TypeError::class);
        $this->expectExceptionMessageMatches('/string.+int/i');
        $this->projectManager()->project();
    }

    #[Test]
    public function http_client_middlewares_can_be_configured(): void
    {
        config(['firebase.projects.app.http_client_options.guzzle_middlewares' => 1]);

        $this->expectException(TypeError::class);
        $this->expectExceptionMessageMatches('/array.+int/i');
        $this->projectManager()->project();
    }

    #[Test]
    public function it_overrides_the_default_firestore_database(): void
    {
        config(['firebase.projects.app.firestore.database' => 1]);

        $this->expectException(TypeError::class);
        $this->expectExceptionMessageMatches('/string.+int/i');
        $this->projectManager()->project()->firestore();
    }

    #[Test]
    public function it_uses_the_laravel_cache_as_auth_token_cache(): void
    {
        config(['firebase.projects.app.cache_store' => 'undefined']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/undefined.+not defined/i');
        $this->projectManager()->project();
    }
}
