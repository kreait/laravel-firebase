<?php

declare(strict_types=1);

namespace Kreait\Laravel\Firebase\Tests;

use Closure;
use Illuminate\Support\Arr;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\FirebaseProject;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Laravel\Firebase\Tests\Resources\TestModel;
use Kreait\Firebase\Exception\Messaging\MessagingError;
use Kreait\Laravel\Firebase\Exception\MessagingChannelException;
use Kreait\Laravel\Firebase\Exception\RuntimeException;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Kreait\Laravel\Firebase\Tests\Resources\TestNotification;
use Kreait\Laravel\Firebase\Notifications\FirebaseMessagingChannel;
use Kreait\Laravel\Firebase\Tests\Resources\InvalidTestNotification;

/**
 * @internal
 */
final class FirebaseMessagingChannelTest extends TestCase
{
    protected function mockMessaging(Closure $mock)
    {
        $messaging = $this->mock(Messaging::class, $mock);

        $project = $this->mock(FirebaseProject::class, function ($mock) use ($messaging) {
            $mock->shouldReceive('messaging')->withNoArgs()->andReturn($messaging);
        });

        Firebase::shouldReceive('project')
            ->withAnyArgs()
            ->andReturn($project);
    }

    protected function getPropertyValue($object, $property)
    {
        return ReflectionObject::createFromInstance($object)
            ->getProperty($property)
            ->getValue($object);
    }

    /** @test */
    public function an_exception_is_thrown_if_to_firebase_messaging_method_is_missing()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Notification is missing toFirebaseMessaging method.');

        (new FirebaseMessagingChannel())->send(new TestModel, new InvalidTestNotification);
    }

    /** @test */
    public function a_cloud_message_can_be_send()
    {
        $this->mockMessaging(function ($mock) {
            $mock->shouldReceive('send')->withArgs(function ($message) {
                if ($message instanceof CloudMessage) {
                    $this->assertEquals('a-valid-firebase-messaging-token', $this->getPropertyValue($message, 'target')->value());
                    $this->assertEquals([
                        'title' => 'A notification title',
                        'body' => 'A notification body',
                    ], $this->getPropertyValue($message, 'notification')->jsonSerialize());

                    return true;
                }
            })->andReturn(['response-key' => 1]);
        });

        $response = (new FirebaseMessagingChannel())->send(new TestModel, new TestNotification);

        $this->assertIsArray($response);
        $this->assertIsArray(Arr::first($response));
        $this->assertArrayHasKey('response-key', Arr::first($response));
    }

    /** @test */
    public function a_cloud_message_can_be_send_to_a_topic()
    {
        $this->mockMessaging(function ($mock) {
            $mock->shouldReceive('send')->withArgs(function ($message) {
                if ($message instanceof CloudMessage) {
                    $this->assertEquals('a-valid-topic', $this->getPropertyValue($message, 'target')->value());
                    $this->assertEquals([
                        'title' => 'A notification title',
                        'body' => 'A notification body',
                    ], $this->getPropertyValue($message, 'notification')->jsonSerialize());

                    return true;
                }
            })->andReturn(['response-key' => 2]);
        });

        $response = (new FirebaseMessagingChannel())->send(new TestModel(true, 'topic'), new TestNotification);

        $this->assertIsArray($response);
        $this->assertIsArray(Arr::first($response));
        $this->assertArrayHasKey('response-key', Arr::first($response));
    }
    /** @test */
    public function a_cloud_message_can_be_send_to_a_condition()
    {
        $this->mockMessaging(function ($mock) {
            $mock->shouldReceive('send')->withArgs(function ($message) {
                if ($message instanceof CloudMessage) {
                    $this->assertEquals("'valid-topic1' in Topics && 'valid-topic2' Topics", $this->getPropertyValue($message, 'target')->value());
                    $this->assertEquals([
                        'title' => 'A notification title',
                        'body' => 'A notification body',
                    ], $this->getPropertyValue($message, 'notification')->jsonSerialize());

                    return true;
                }
            })->andReturn(['response-key' => 2]);
        });

        $response = (new FirebaseMessagingChannel())->send(new TestModel(true, 'condition'), new TestNotification);

        $this->assertIsArray($response);
        $this->assertIsArray(Arr::first($response));
        $this->assertArrayHasKey('response-key', Arr::first($response));
    }

    /** @test */
    public function nothing_is_done_when_no_token_is_returned()
    {
        $response = (new FirebaseMessagingChannel())->send(new TestModel(true, 'empty'), new TestNotification);

        $this->assertIsArray($response);
        $this->assertEmpty($response);
    }

    /** @test */
    public function multiple_tokens_are_chunked()
    {
        $this->mockMessaging(function ($mock) {
            $mock->shouldReceive('sendMulticast')->withArgs(function ($message) {
                if ($message instanceof CloudMessage) {
                    $this->assertEquals([
                        'title' => 'A notification title',
                        'body' => 'A notification body',
                    ], $this->getPropertyValue($message, 'notification')->jsonSerialize());

                    return true;
                }
            })->andReturn(MulticastSendReport::withItems([]));
        });

        $response = (new FirebaseMessagingChannel())->send(new TestModel(true, 'token', TestModel::FLAVOR_UNUSED_OR_TWO_TOKENS), new TestNotification);

        $this->assertIsArray($response);
        $this->assertInstanceOf(MulticastSendReport::class, Arr::first($response));
    }

    /** @test */
    public function a_invalid_message_triggers_an_exception()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/^Message must be implementing/');

        (new FirebaseMessagingChannel())->send(new TestModel(), new TestNotification(TestNotification::FLAVOR_INVALID_MESSAGE));
    }

    /** @test */
    public function a_invalid_custom_message_object_triggers_an_exception()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/^Message class .*? should implement a withChangedTarget method accepting a target type and value\.$/');

        (new FirebaseMessagingChannel())->send(new TestModel(), new TestNotification(TestNotification::FLAVOR_INVALID_CUSTOM_MESSAGE_OBJECT));
    }

    /** @test */
    public function failed_method_will_be_called_on_exception()
    {
        $this->mockMessaging(function ($mock) {
            $mock->shouldReceive('send')->withArgs(function ($message) {
                if ($message instanceof CloudMessage) {
                    return true;
                }
            })->andThrows(new MessagingError('A messaging error is thrown.'));
        });

        $this->expectException(MessagingChannelException::class);
        $this->expectExceptionMessage('Unable to send notification.');

        (new FirebaseMessagingChannel())->send(new TestModel, $notification = new TestNotification);

        $this->assertNotNull($notification->thrownException);
        $this->assertInstanceOf(MessagingError::class, $notification->thrownException);
        $this->assertEquals('A messaging error is thrown.', $notification->thrownException->getMessage());
    }
}
