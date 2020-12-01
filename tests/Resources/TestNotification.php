<?php

namespace Kreait\Laravel\Firebase\Tests\Resources;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Exception\MessagingException;

class TestNotification extends Notification
{
    public const FLAVOR_NOTIFICATION = 0;
    public const FLAVOR_INVALID_MESSAGE = 1;
    public const FLAVOR_INVALID_CUSTOM_MESSAGE_OBJECT = 2;
    public const FLAVOR_VALID_CUSTOM_MESSAGE_OBJECT = 3;

    protected int $flavor = 0;
    public ?MessagingException $thrownException = null;

    public function __construct(int $flavor = 0)
    {
        $this->flavor = $flavor;
    }

    public function toFirebaseMessaging($notifiable)
    {
        if ($this->flavor === self::FLAVOR_INVALID_MESSAGE) {
            return 'invalid-message';
        }

        if ($this->flavor === self::FLAVOR_INVALID_CUSTOM_MESSAGE_OBJECT) {
            return new class implements Message {
                public function jsonSerialize()
                {
                    return [];
                }
            };
        }

        if ($this->flavor === self::FLAVOR_VALID_CUSTOM_MESSAGE_OBJECT) {
            return new class implements Message {
                protected ?array $target = null;

                public function withChangedTarget($type, $value) {
                    $this->target = [$type => $value];
                    return $this;
                }

                public function jsonSerialize()
                {
                    return $this->target ?? [];
                }
            };
        }


        return [
            'notification' => [
                'title' => 'A notification title',
                'body' => 'A notification body',
            ],
        ];
    }

    public function firebaseMessagingFailed($notifiable, MessagingException $exception): void
    {
        $this->thrownException = $exception;
    }
}
