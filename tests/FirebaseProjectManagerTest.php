<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Tests;

use Kreait\Firebase;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Factory;
use Kreait\Laravel\Firebase\FirebaseProjectManager;
use Roave\BetterReflection\Reflection\ReflectionObject;

/**
 * @internal
 */
final class FirebaseProjectManagerTest extends TestCase
{
    protected function factoryForProject(string $project = null): Factory
    {
        $manager = $this->app->make(FirebaseProjectManager::class);
        $project = $manager->project($project);

        $factory = ReflectionObject::createFromInstance($project)->getProperty('factory');

        return $factory->getValue($project);
    }

    /** @test */
    public function a_project_configuration_has_to_exist(): void
    {
        $manager = $this->app->make(FirebaseProjectManager::class);

        $projectName = 'non-existent-project-name';

        $this->expectException(InvalidArgumentException::class);

        ReflectionObject::createFromInstance($manager)
            ->getMethod('configuration')
            ->invoke($manager, $projectName);
    }

    /** @test */
    public function a_default_project_can_be_set(): void
    {
        $manager = $this->app->make(FirebaseProjectManager::class);

        $projectName = 'another-app';

        $manager->setDefaultProject($projectName);

        $this->assertEquals($projectName, $manager->getDefaultProject());
        $this->assertEquals($projectName, $this->app->config->get('firebase.default'), 'default project should be set in config');
    }

    /** @test */
    public function calls_are_passed_to_default_project(): void
    {
        $manager = $this->app->make(FirebaseProjectManager::class);

        $projectName = $this->app->config->get('firebase.default');

        $this->assertSame($manager->project($projectName)->auth(), $manager->auth());
    }

    /** @test */
    public function credentials_can_be_configured(): void
    {
        // Reference credentials
        $credentialsPath = realpath(__DIR__.'/_fixtures/service_account.json');
        $credentials = json_decode(file_get_contents($credentialsPath), true);

        // Set configuration and retrieve project
        $projectName = 'app';
        $this->app->config->set('firebase.projects.'.$projectName.'.credentials.file', realpath(__DIR__.'/_fixtures/service_account.json'));
        $factory = $this->factoryForProject($projectName);

        // Retrieve service account
        /** @var Firebase\ServiceAccount $serviceAccount */
        $serviceAccount = ReflectionObject::createFromInstance($factory)
            ->getMethod('getServiceAccount')
            ->invoke($factory);

        // Validate value
        $this->assertSame($credentials, $serviceAccount->asArray());
    }

    /** @test */
    public function projects_can_have_different_credentials(): void
    {
        // Reference credentials
        $credentialsPath = realpath(__DIR__.'/_fixtures/service_account.json');
        $credentials = json_decode(file_get_contents($credentialsPath), true);

        $secondCredentialsPath = realpath(__DIR__.'/_fixtures/another_service_account.json');
        $secondCredentials = json_decode(file_get_contents($secondCredentialsPath), true);

        // Project names to use
        $projectName = 'app';
        $secondProjectName = 'another-app';

        // Set service accounts explicitly
        $this->app->config->set('firebase.projects.'.$projectName.'.credentials.file', realpath(__DIR__.'/_fixtures/service_account.json'));
        $this->app->config->set('firebase.projects.'.$secondProjectName.'.credentials.file', realpath(__DIR__.'/_fixtures/another_service_account.json'));

        // Retrieve factories and service accounts
        $factory = $this->factoryForProject($projectName);
        $secondFactory = $this->factoryForProject($secondProjectName);

        /** @var Firebase\ServiceAccount $serviceAccount */
        $serviceAccount = ReflectionObject::createFromInstance($factory)
            ->getMethod('getServiceAccount')
            ->invoke($factory);

        /** @var Firebase\ServiceAccount $secondServiceAccount */
        $secondServiceAccount = ReflectionObject::createFromInstance($secondFactory)
            ->getMethod('getServiceAccount')
            ->invoke($secondFactory);

        // Validate values
        $this->assertSame($credentials, $serviceAccount->asArray());
        $this->assertSame($secondCredentials, $secondServiceAccount->asArray());
    }

    /** @test */
    public function credential_auto_discovery_is_enabled_by_default_for_default_project(): void
    {
        $projectName = $this->app->config->get('firebase.default');

        $factory = $this->factoryForProject($projectName);

        $property = ReflectionObject::createFromInstance($factory)->getProperty('discoveryIsDisabled');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertFalse($property->getValue($factory));
    }

    /** @test */
    public function credential_auto_discovery_can_be_disabled_for_default_project(): void
    {
        $projectName = $this->app->config->get('firebase.default');

        $this->app->config->set('firebase.projects.'.$projectName.'.credentials.auto_discovery', false);

        $factory = $this->factoryForProject($projectName);

        $property = ReflectionObject::createFromInstance($factory)->getProperty('discoveryIsDisabled');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertTrue($property->getValue($factory));
    }

    /** @test */
    public function credential_auto_discovery_is_not_enabled_by_default_for_other_projects(): void
    {
        $projectName = 'another-app';

        $this->app->config->set('firebase.projects.'.$projectName.'.credentials', []);

        $factory = $this->factoryForProject($projectName); // factory for default project with default settings

        $property = ReflectionObject::createFromInstance($factory)->getProperty('discoveryIsDisabled');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertTrue($property->getValue($factory));
    }

    /** @test */
    public function credential_auto_discovery_can_be_enabled_for_other_project(): void
    {
        $projectName = 'another-app';

        $this->app->config->set('firebase.projects.'.$projectName.'.credentials.auto_discovery', true);

        $factory = $this->factoryForProject($projectName);

        $property = ReflectionObject::createFromInstance($factory)->getProperty('discoveryIsDisabled');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertFalse($property->getValue($factory));
    }

    /** @test */
    public function the_realtime_database_url_can_be_configured(): void
    {
        $projectName = $this->app->config->get('firebase.default');
        $this->app->config->set('firebase.projects.'.$projectName.'.database.url', $url = 'https://domain.tld');

        $database = $this->app->make(Firebase\Database::class);

        $property = ReflectionObject::createFromInstance($database)->getProperty('uri');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertSame($url, (string) $property->getValue($database));
    }

    /** @test */
    public function the_dynamic_links_default_domain_can_be_configured(): void
    {
        $projectName = $this->app->config->get('firebase.default');
        $this->app->config->set('firebase.projects.'.$projectName.'.dynamic_links.default_domain', $domain = 'https://domain.tld');

        $dynamicLinks = $this->app->make(Firebase\DynamicLinks::class);

        $property = ReflectionObject::createFromInstance($dynamicLinks)->getProperty('defaultDynamicLinksDomain');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        /** @var Firebase\Value\Url $configuredDomain */
        $configuredDomain = $property->getValue($dynamicLinks);

        $this->assertSame($domain, (string) $configuredDomain->toUri());
    }

    /** @test */
    public function the_storage_default_bucket_can_be_configured(): void
    {
        $projectName = $this->app->config->get('firebase.default');
        $this->app->config->set('firebase.projects.'.$projectName.'.storage.default_bucket', $name = 'my-bucket');

        $storage = $this->app->make(Firebase\Storage::class);

        $property = ReflectionObject::createFromInstance($storage)->getProperty('defaultBucket');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertSame($name, $property->getValue($storage));
    }

    /** @test */
    public function logging_can_be_configured(): void
    {
        $projectName = $this->app->config->get('firebase.default');
        $this->app->config->set('firebase.projects.'.$projectName.'.logging.http_log_channel', 'stack');

        $factory = $this->factoryForProject($projectName);

        $property = ReflectionObject::createFromInstance($factory)->getProperty('httpLogMiddleware');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertNotNull($property->getValue($factory));
    }

    /** @test */
    public function debug_logging_can_be_configured(): void
    {
        $projectName = $this->app->config->get('firebase.default');
        $this->app->config->set('firebase.projects.'.$projectName.'.logging.http_debug_log_channel', 'stack');

        $factory = $this->factoryForProject($projectName);

        $property = ReflectionObject::createFromInstance($factory)->getProperty('httpDebugLogMiddleware');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertNotNull($property->getValue($factory));
    }

    /** @test */
    public function it_uses_the_laravel_cache(): void
    {
        $projectName = $this->app->config->get('firebase.default');
        $factory = $this->factoryForProject($projectName);

        $property = ReflectionObject::createFromInstance($factory)->getProperty('verifierCache');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertInstanceOf(\Illuminate\Contracts\Cache\Repository::class, $property->getValue($factory));
    }

    /** @test */
    public function enabling_debug_with_a_boolean_triggers_a_deprecation(): void
    {
        $this->expectException(\Throwable::class);
        $this->expectExceptionMessageMatches('/deprecated/');

        $projectName = $this->app->config->get('firebase.default');

        $this->app->config->set('firebase.projects.'.$projectName.'.debug', true);

        $factory = $this->factoryForProject($projectName);
    }
}
