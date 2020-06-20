<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Tests;

use Kreait\Firebase;
use Roave\BetterReflection\Reflection\ReflectionObject;

/**
 * @internal
 */
final class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_provides_components(): void
    {
        $this->app->config->set('firebase.credentials.file', realpath(__DIR__.'/_fixtures/service_account.json'));

        $this->assertInstanceOf(Firebase\Auth::class, $this->app->make(Firebase\Auth::class));
        $this->assertInstanceOf(Firebase\Database::class, $this->app->make(Firebase\Database::class));
        $this->assertInstanceOf(Firebase\DynamicLinks::class, $this->app->make(Firebase\DynamicLinks::class));
        $this->assertInstanceOf(Firebase\Messaging::class, $this->app->make(Firebase\Messaging::class));
        $this->assertInstanceOf(Firebase\RemoteConfig::class, $this->app->make(Firebase\RemoteConfig::class));
        $this->assertInstanceOf(Firebase\Storage::class, $this->app->make(Firebase\Storage::class));
    }

    /** @test */
    public function it_does_not_provide_optional_components(): void
    {
        $this->expectException(\Throwable::class);
        $this->expectDeprecationMessageMatches('/unable/i');

        $this->app->make(Firebase\Firestore::class);
    }

    /** @test */
    public function credentials_can_be_configured(): void
    {
        $credentialsPath = realpath(__DIR__.'/_fixtures/service_account.json');
        $credentials = json_decode(file_get_contents($credentialsPath), true);

        $this->app->config->set('firebase.credentials.file', realpath(__DIR__.'/_fixtures/service_account.json'));
        $factory = $this->app->make(Firebase\Factory::class);

        /** @var Firebase\ServiceAccount $serviceAccount */
        $serviceAccount = ReflectionObject::createFromInstance($factory)
            ->getMethod('getServiceAccount')
            ->invoke($factory);

        $this->assertSame($credentials, $serviceAccount->asArray());
    }

    /** @test */
    public function credential_auto_discovery_is_enabled_by_default(): void
    {
        $factory = $this->app->make(Firebase\Factory::class);

        $property = ReflectionObject::createFromInstance($factory)->getProperty('discoveryIsDisabled');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertFalse($property->getValue($factory));
    }

    /** @test */
    public function credential_auto_discovery_can_be_disabled(): void
    {
        $this->app->config->set('firebase.credentials.auto_discovery', false);

        $factory = $this->app->make(Firebase\Factory::class);

        $property = ReflectionObject::createFromInstance($factory)->getProperty('discoveryIsDisabled');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertTrue($property->getValue($factory));
    }

    /** @test */
    public function the_realtime_database_url_can_be_configured(): void
    {
        $this->app->config->set('firebase.database.url', $url = 'https://domain.tld');

        $database = $this->app->make(Firebase\Database::class);

        $property = ReflectionObject::createFromInstance($database)->getProperty('uri');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertSame($url, (string) $property->getValue($database));
    }

    /** @test */
    public function the_dynamic_links_default_domain_can_be_configured(): void
    {
        $this->app->config->set('firebase.dynamic_links.default_domain', $domain = 'https://domain.tld');

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
        $this->app->config->set('firebase.storage.default_bucket', $name = 'my-bucket');

        $storage = $this->app->make(Firebase\Storage::class);

        $property = ReflectionObject::createFromInstance($storage)->getProperty('defaultBucket');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertSame($name, $property->getValue($storage));
    }

    /** @test */
    public function logging_can_be_configured(): void
    {
        $this->app->config->set('firebase.logging.http_log_channel', 'stack');

        $factory = $this->app->make(Firebase\Factory::class);

        $property = ReflectionObject::createFromInstance($factory)->getProperty('httpLogMiddleware');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertNotNull($property->getValue($factory));
    }

    /** @test */
    public function debug_logging_can_be_configured(): void
    {
        $this->app->config->set('firebase.logging.http_debug_log_channel', 'stack');

        $factory = $this->app->make(Firebase\Factory::class);

        $property = ReflectionObject::createFromInstance($factory)->getProperty('httpDebugLogMiddleware');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertNotNull($property->getValue($factory));
    }

    /** @test */
    public function it_uses_the_laravel_cache(): void
    {
        $factory = $this->app->make(Firebase\Factory::class);

        $property = ReflectionObject::createFromInstance($factory)->getProperty('verifierCache');
        $property->setVisibility(\ReflectionProperty::IS_PUBLIC);

        $this->assertInstanceOf(\Illuminate\Contracts\Cache\Repository::class, $property->getValue($factory));
    }

    /** @test */
    public function enabling_debug_with_a_boolean_triggers_a_deprecation(): void
    {
        $this->expectException(\Throwable::class);
        $this->expectExceptionMessageMatches('/deprecated/');

        $this->app->config->set('firebase.debug', true);
        $this->app->make(Firebase\Factory::class);
    }
}
