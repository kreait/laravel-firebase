<?php

namespace Kreait\Laravel\Firebase\Notifications;

use Illuminate\Support\Arr;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\Message;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Laravel\Firebase\Exception\RuntimeException;
use Kreait\Laravel\Firebase\Exception\InvalidArgumentException;
use Kreait\Laravel\Firebase\Exception\MessagingChannelException;

class FirebaseMessagingChannel
{
    /**
     * Send notification
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return void
     *
     * @throws \Kreait\Laravel\Firebase\Exception\RuntimeException
     */
    public function send($notifiable, Notification $notification): array
    {
        // Get the message
        $message = $this->getMessage($notifiable, $notification);

        // Get the target
        [$targetType, $targetValue] = $this->getTarget($notifiable, $notification);

        // Check if there is a target, otherwise return an empty array
        if (! $targetValue || (is_array($targetValue) && count($targetValue) < 1)) {
            return [];
        }

        // Send the message
        try {
            // Send multicast
            if ($targetType === MessageTarget::TOKEN && count($targetValue) > 1) {

                $chunkedTokens = array_chunk($targetValue, 10);

                $responses = [];
                foreach ($chunkedTokens as $chunkedToken) {
                    $responses[] = $this->getFirebaseMessaging($notifiable, $notification)->sendMulticast($message, $chunkedToken);
                }

                return $responses;
            }

            // Set target and send
            if (! method_exists($message, 'withChangedTarget')) {
                throw new RuntimeException('Message class "'.get_class($message).'" should implement a withChangedTarget method accepting a target type and value.');
            }

            return [
                $this->getFirebaseMessaging($notifiable, $notification)->send(
                    $message->withChangedTarget($targetType, $targetValue)
                )
            ];
        } catch (MessagingException $e) {
            if (method_exists($notification, 'firebaseMessagingFailed')) {
                $notification->firebaseMessagingFailed($notifiable, $e);
            }

            throw MessagingChannelException::fromMessagingException('Unable to send notification.', $e);
        }
    }

    /**
     * Get message from notification
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return \Kreait\Firebase\Messaging\Message
     *
     * @throws \Kreait\Laravel\Firebase\Exception\RuntimeException
     */
    protected function getMessage($notifiable, Notification $notification): Message
    {
        if (! method_exists($notification, 'toFirebaseMessaging')) {
            throw new RuntimeException('Notification is missing toFirebaseMessaging method.');
        }

        $message = $notification->toFirebaseMessaging($notifiable);

        if (is_array($message)) {
            return CloudMessage::fromArray($message);
        }

        if (! $message instanceof Message) {
            throw new RuntimeException('Message must be implementing '.Message::class);
        }

        return $message;
    }

    /**
     * Get target from notifiable
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return array
     *
     * @throws \Kreait\Laravel\Firebase\Exception\InvalidArgumentException
     */
    protected function getTarget($notifiable, Notification $notification): array
    {
        $targetType = $notifiable->routeNotificationFor('firebaseMessagingTarget', $notification);

        switch ($targetType) {
            case MessageTarget::CONDITION:
                $targetValue = $notifiable->routeNotificationFor('firebaseMessagingCondition', $notification);
                break;
            case MessageTarget::TOKEN:
            case null:
                $targetValue = Arr::wrap($notifiable->routeNotificationFor('firebaseMessagingToken', $notification));
                break;
            case MessageTarget::TOPIC:
                $targetValue = $notifiable->routeNotificationFor('firebaseMessagingTopic', $notification);
                break;
            default:
                throw new InvalidArgumentException('Target "'.$targetType.'" is invalid.');
        }

        return [
            strtolower($targetType ?? 'token'),
            $targetValue,
        ];
    }

    /**
     * Get firebase messaging instance for the correct project
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return \Kreait\Firebase\Messaging
     */
    protected function getFirebaseMessaging($notifiable, Notification $notification): Messaging
    {
        return Firebase::project(
            $notifiable->routeNotificationFor('firebaseMessagingProject', $notification) ?? null
        )->messaging();
    }
}
