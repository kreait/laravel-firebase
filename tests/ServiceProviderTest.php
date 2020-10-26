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
        $this->app->config->set('firebase.projects.app.credentials.file', realpath(__DIR__.'/_fixtures/service_account.json'));

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
}
