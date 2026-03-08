<?php

use App\Models\PushSubscription;
use App\Services\DailyDigestNotificationService;
use App\Services\WebPushNotifier;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $subscriptions = PushSubscription::query()->get();
    if ($subscriptions->isEmpty()) {
        return;
    }

    $payload = app(DailyDigestNotificationService::class)->morningPayload();
    app(WebPushNotifier::class)->send($subscriptions, $payload);
})->dailyAt('07:20')->timezone('America/Martinique')->name('finance-digest-morning');

Schedule::call(function () {
    $subscriptions = PushSubscription::query()->get();
    if ($subscriptions->isEmpty()) {
        return;
    }

    $payload = app(DailyDigestNotificationService::class)->eveningPayload();
    app(WebPushNotifier::class)->send($subscriptions, $payload);
})->dailyAt('21:00')->timezone('America/Martinique')->name('finance-digest-evening');
