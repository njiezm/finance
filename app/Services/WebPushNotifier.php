<?php

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushNotifier
{
    public function send(iterable $subscriptions, array $payload): array
    {
        $publicKey = config('services.webpush.public_key');
        $privateKey = config('services.webpush.private_key');
        $subject = config('services.webpush.subject');

        if (! $publicKey || ! $privateKey || ! $subject) {
            return ['sent' => 0, 'failed' => 0, 'error' => 'VAPID keys missing'];
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => $subject,
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ]);

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->endpoint,
                'publicKey' => $sub->public_key,
                'authToken' => $sub->auth_token,
                'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
            ]);

            $webPush->queueNotification($subscription, $jsonPayload);
        }

        $sent = 0;
        $failed = 0;

        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            if ($report->isSuccess()) {
                $sent++;
                continue;
            }

            $failed++;

            if ($report->isSubscriptionExpired()) {
                PushSubscription::query()->where('endpoint', $endpoint)->delete();
            }
        }

        return ['sent' => $sent, 'failed' => $failed, 'error' => null];
    }
}
