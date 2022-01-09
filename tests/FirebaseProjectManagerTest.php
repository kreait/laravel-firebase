<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Tests;

use Kreait\Firebase;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Factory;
use Kreait\Laravel\Firebase\FirebaseProjectManager;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionObject;

/**
 * @internal
 */
final class FirebaseProjectManagerTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('firebase.projects.app.credentials.file', __DIR__.'/_fixtures/service_account.json');
    }

    /**
     * @test
     */
    public function a_project_configuration_has_to_exist(): void
    {
        $manager = $this->app->make(FirebaseProjectManager::class);

        $projectName = 'non-existent-project-name';

        $this->expectException(InvalidArgumentException::class);

        $this->getAccessibleMethod($manager, 'configuration')->invoke($manager, $projectName);
    }

    /**
     * @test
     */
    public function a_default_project_can_be_set(): void
    {
        $manager = $this->app->make(FirebaseProjectManager::class);

        $projectName = 'another-app';

        $manager->setDefaultProject($projectName);

        $this->assertSame($projectName, $manager->getDefaultProject());
        $this->assertSame($projectName, $this->app->config->get('firebase.default'), 'default project should be set in config');
    }

    /**
     * @test
     */
    public function calls_are_passed_to_default_project(): void
    {
        $manager = $this->app->make(FirebaseProjectManager::class);

        $projectName = $this->app->config->get('firebase.default');

        $this->assertSame($manager->project($projectName)->auth(), $manager->auth());
    }

    /**
     * @test
     */
    public function credentials_can_be_configured(): void
    {
        // Reference credentials
        $credentialsPath = \realpath(__DIR__.'/_fixtures/service_account.json');
        $credentials = \json_decode(\file_get_contents($credentialsPath), true);

        // Set configuration and retrieve project
        $projectName = 'app';
        $this->app->config->set('firebase.projects.'.$projectName.'.credentials.file', \realpath(__DIR__.'/_fixtures/service_account.json'));
        $factory = $this->factoryForProject($projectName);

        // Retrieve service account
        /** @var Firebase\ServiceAccount $serviceAccount */
        $serviceAccount = $this->getAccessibleMethod($factory, 'getServiceAccount')->invoke($factory);

        // Validate value
        $this->assertSame($credentials, $serviceAccount->asArray());
    }

    /**
     * @test
     */
    public function a_tenant_id_can_be_set(): void
    {
        $this->app->config->set('firebase.projects.app.auth.tenant_id', $expected = 'abc123');

        $auth = $this->app->make(Firebase\Contract\Auth::class);

        $tenantId = $this->getAccessibleProperty($auth, 'tenantId')->getValue($auth);

        $this->assertSame($expected, $tenantId);
    }

    /**
     * @test
     */
    public function projects_can_have_different_credentials(): void
    {
        // Reference credentials
        $credentialsPath = \realpath(__DIR__.'/_fixtures/service_account.json');
        $credentials = \json_decode(\file_get_contents($credentialsPath), true);

        $secondCredentialsPath = \realpath(__DIR__.'/_fixtures/another_service_account.json');
        $secondCredentials = \json_decode(\file_get_contents($secondCredentialsPath), true);

        // Project names to use
        $projectName = 'app';
        $secondProjectName = 'another-app';

        // Set service accounts explicitly
        $this->app->config->set('firebase.projects.'.$projectName.'.credentials.file', \realpath(__DIR__.'/_fixtures/service_account.json'));
        $this->app->config->set('firebase.projects.'.$secondProjectName.'.credentials.file', \realpath(__DIR__.'/_fixtures/another_service_account.json'));

        // Retrieve factories and service accounts
        $factory = $this->factoryForProject($projectName);
        $secondFactory = $this->factoryForProject($secondProjectName);

        /** @var Firebase\ServiceAccount $serviceAccount */
        $serviceAccount = $this->getAccessibleMethod($factory, 'getServiceAccount')->invoke($factory);

        /** @var Firebase\ServiceAccount $secondServiceAccount */
        $secondServiceAccount = $this->getAccessibleMethod($factory, 'getServiceAccount')->invoke($secondFactory);

        // Validate values
        $this->assertSame($credentials, $serviceAccount->asArray());
        $this->assertSame($secondCredentials, $secondServiceAccount->asArray());
    }

    /**
     * @test
     */
    public function credential_auto_discovery_is_enabled_by_default_for_default_project(): void
    {
        $projectName = $this->app->config->get('firebase.default');

        $factory = $this->factoryForProject($projectName);

        $this->getAccessibleMethod($factory, 'getServiceAccount')->invoke($factory);

        $property = $this->getAccessibleProperty($factory, 'discoveryIsDisabled');

        $this->assertFalse($property->getValue($factory));
    }

    /**
     * @test
     */
    public function credential_auto_discovery_can_be_disabled_for_default_project(): void
    {
        $projectName = $this->app->config->get('firebase.default');

        $this->app->config->set('firebase.projects.'.$projectName.'.credentials.auto_discovery', false);

        $factory = $this->factoryForProject($projectName);

        $property = $this->getAccessibleProperty($factory, 'discoveryIsDisabled');

        $this->assertTrue($property->getValue($factory));
    }

    /**
     * @test
     */
    public function credential_auto_discovery_is_not_enabled_by_default_for_other_projects(): void
    {
        $projectName = 'another-app';

        $this->app->config->set('firebase.projects.'.$projectName.'.credentials', []);

        $factory = $this->factoryForProject($projectName); // factory for default project with default settings

        $property = $this->getAccessibleProperty($factory, 'discoveryIsDisabled');

        $this->assertTrue($property->getValue($factory));
    }

    /**
     * @test
     */
    public function credential_auto_discovery_can_be_enabled_for_other_project(): void
    {
        $projectName = 'another-app';

        $this->app->config->set('firebase.projects.'.$projectName.'.credentials.auto_discovery', true);

        $factory = $this->factoryForProject($projectName);

        $property = $this->getAccessibleProperty($factory, 'discoveryIsDisabled');

        $this->assertFalse($property->getValue($factory));
    }

    /**
     * @test
     */
    public function the_realtime_database_url_can_be_configured(): void
    {
        $projectName = $this->app->config->get('firebase.default');
        $this->app->config->set('firebase.projects.'.$projectName.'.database.url', $url = 'https://domain.tld');
        $this->app->config->set('firebase.projects.'.$projectName.'.database.auth_variable_override', ['uid' => 'some-uid']);

        $database = $this->app->make(Firebase\Contract\Database::class);

        $property = $this->getAccessibleProperty($database, 'uri');

        $this->assertSame($url, (string) $property->getValue($database));
    }

    /**
     * @test
     */
    public function the_dynamic_links_default_domain_can_be_configured(): void
    {
        $projectName = $this->app->config->get('firebase.default');
        $this->app->config->set('firebase.projects.'.$projectName.'.dynamic_links.default_domain', $domain = 'https://domain.tld');

        $dynamicLinks = $this->app->make(Firebase\Contract\DynamicLinks::class);

        $property = $this->getAccessibleProperty($dynamicLinks, 'defaultDynamicLinksDomain');

        $configuredDomain = $property->getValue($dynamicLinks);

        $this->assertSame($domain, $configuredDomain);
    }

    /**
     * @test
     */
    public function the_storage_default_bucket_can_be_configured(): void
    {
        $projectName = $this->app->config->get('firebase.default');
        $this->app->config->set('firebase.projects.'.$projectName.'.storage.default_bucket', $name = 'my-bucket');

        $storage = $this->app->make(Firebase\Contract\Storage::class);

        $property = $this->getAccessibleProperty($storage, 'defaultBucket');

        $this->assertSame($name, $property->getValue($storage));
    }

    /**
     * @test
     */
    public function logging_can_be_configured(): void
    {
        $projectName = $this->app->config->get('firebase.default');
        $this->app->config->set('firebase.projects.'.$projectName.'.logging.http_log_channel', 'stack');

        $factory = $this->factoryForProject($projectName);

        $property = $this->getAccessibleProperty($factory, 'httpLogMiddleware');

        $this->assertNotNull($property->getValue($factory));
    }

    /**
     * @test
     */
    public function debug_logging_can_be_configured(): void
    {
        $projectName = $this->app->config->get('firebase.default');
        $this->app->config->set('firebase.projects.'.$projectName.'.logging.http_debug_log_channel', 'stack');

        $factory = $this->factoryForProject($projectName);

        $property = $this->getAccessibleProperty($factory, 'httpDebugLogMiddleware');

        $this->assertNotNull($property->getValue($factory));
    }

    /**
     * @test
     */
    public function http_client_options_can_be_configured(): void
    {
        $projectName = $this->app->config->get('firebase.default');
        $this->app->config->set('firebase.projects.'.$projectName.'.http_client_options.proxy', 'proxy.domain.tld');
        $this->app->config->set('firebase.projects.'.$projectName.'.http_client_options.timeout', 1.23);

        $factory = $this->factoryForProject($projectName);

        /** @var Firebase\Http\HttpClientOptions $httpClientOptions */
        $httpClientOptions = $this->getAccessibleProperty($factory, 'httpClientOptions')->getValue($factory);

        $this->assertSame('proxy.domain.tld', $httpClientOptions->proxy());
        $this->assertSame(1.23, $httpClientOptions->timeout());
    }

    /**
     * @test
     */
    public function it_uses_the_laravel_cache_as_verifier_cache(): void
    {
        $projectName = $this->app->config->get('firebase.default');
        $factory = $this->factoryForProject($projectName);

        $property = $this->getAccessibleProperty($factory, 'verifierCache');

        $this->assertInstanceOf(CacheItemPoolInterface::class, $property->getValue($factory));
    }

    /**
     * @test
     */
    public function it_uses_the_laravel_cache_as_auth_token_cache(): void
    {
        $projectName = $this->app->config->get('firebase.default');
        $factory = $this->factoryForProject($projectName);

        $property = $this->getAccessibleProperty($factory, 'authTokenCache');

        $this->assertInstanceOf(CacheItemPoolInterface::class, $property->getValue($factory));
    }

    private function factoryForProject(?string $project = null): Factory
    {
        $project = $this->app->make(FirebaseProjectManager::class)->project($project);

        return $this->getAccessibleProperty($project, 'factory')->getValue($project);
    }

    private function getAccessibleProperty(object $object, string $propertyName): \ReflectionProperty
    {
        $property = (new ReflectionObject($object))->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }

    private function getAccessibleMethod(object $object, string $methodName): \ReflectionMethod
    {
        $property = (new ReflectionObject($object))->getMethod($methodName);
        $property->setAccessible(true);

        return $property;
    }
}
