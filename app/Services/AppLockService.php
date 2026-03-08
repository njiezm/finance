<?php

namespace App\Services;

use Illuminate\Http\Request;

class AppLockService
{
    public static function expectedPassword(): string
    {
        return (string) env('APP_ACCESS_PASSWORD', 'NjieZM972-');
    }

    public static function isValidPassword(string $password): bool
    {
        return hash_equals(self::expectedPassword(), $password);
    }

    public static function rememberToken(Request $request): string
    {
        $key = (string) config('app.key');
        $fingerprint = (string) $request->userAgent();

        return hash_hmac('sha256', $fingerprint, $key);
    }
}
