<?php

namespace App\Http\Controllers\Push;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use App\Services\WebPushNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function __construct(private readonly WebPushNotifier $notifier)
    {
    }

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'contentEncoding' => ['nullable', 'string'],
        ]);

        PushSubscription::query()->updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            [
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => $validated['contentEncoding'] ?? 'aesgcm',
                'user_agent' => (string) $request->userAgent(),
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url'],
        ]);

        PushSubscription::query()->where('endpoint', $validated['endpoint'])->delete();

        return response()->json(['ok' => true]);
    }

    public function sendTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['nullable', 'url'],
        ]);

        $subscriptions = isset($validated['endpoint'])
            ? PushSubscription::query()->where('endpoint', $validated['endpoint'])->get()
            : PushSubscription::query()->get();

        $result = $this->notifier->send($subscriptions, [
            'title' => 'Finance Perso Elite',
            'body' => 'Notifications push actives. Configuration terminee.',
            'url' => url('/'),
            'icon' => url('/icons/icon.svg'),
            'badge' => url('/icons/icon.svg'),
            'tag' => 'fp-test',
        ]);

        return response()->json([
            'ok' => $result['error'] === null,
            'sent' => $result['sent'],
            'failed' => $result['failed'],
            'error' => $result['error'],
        ]);
    }
}
