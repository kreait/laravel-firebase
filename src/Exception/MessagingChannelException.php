<?php

namespace Kreait\Laravel\Firebase\Exception;

use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;

class MessagingChannelException extends \RuntimeException implements FirebaseException
{
    public static function fromMessagingException(string $message, MessagingException $e)
    {
        return new static($message, $e->getCode(), $e);
    }
}
