<?php

namespace Kreait\Laravel\Firebase\Tests\Resources;

use Illuminate\Notifications\Notifiable;
use Kreait\Firebase\Messaging\MessageTarget;

class TestModel
{
    use Notifiable;

    public const FLAVOR_UNUSED_OR_SINGLE_TOKEN = 0;
    public const FLAVOR_UNUSED_OR_TWO_TOKENS = 1;

    protected bool $validTarget = true;
    protected ?string $tokenType = null;
    protected int $flavor = 0;

    public function __construct(bool $validTarget = true, string $tokenType = null, int $flavor = 0)
    {
        $this->validTarget = $validTarget;
        $this->tokenType = in_array($tokenType, array_merge(MessageTarget::TYPES, ['empty'])) ? $tokenType : null;
        $this->flavor = $flavor;
    }

    public function routeNotificationForFirebaseMessagingType(): ?string
    {
        return $this->tokenType === 'empty' ? null : $this->tokenType;
    }

    public function routeNotificationForFirebaseMessaging($notification)
    {
        if (is_null($this->tokenType) || ($this->tokenType === MessageTarget::TOKEN && $this->flavor === self::FLAVOR_UNUSED_OR_SINGLE_TOKEN)) {
            return $this->validTarget
                ? 'a-valid-firebase-messaging-token'
                : 'an-invalid-firebase-messaging-token';
        }

        if ($this->tokenType === MessageTarget::TOKEN) {
            if($this->flavor === self::FLAVOR_UNUSED_OR_TWO_TOKENS) {
                return $this->validTarget
                    ? ['valid-token-1', 'valid-token2']
                    : ['invalid-token-2', 'invalid-token-2'];
            }
        }

        if ($this->tokenType === MessageTarget::CONDITION) {
            return $this->validTarget
                ? "'valid-topic1' in Topics && 'valid-topic2' Topics"
                : 'an invalid condition!';
        }

        if ($this->tokenType === MessageTarget::TOPIC) {
            return $this->validTarget
                ? 'a-valid-topic'
                : 'an-invalid-topic';
        }

        if ($this->tokenType === 'empty') {
            return null;
        }

        return null;
    }
}
