<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AppLockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AppLockController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        if (! AppLockService::isValidPassword($validated['password'])) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.'])->withInput();
        }

        $request->session()->put('app_unlocked', true);

        $response = redirect()->intended(route('overview'));

        if ((bool) ($validated['remember'] ?? false)) {
            $response->cookie('fp_remember', AppLockService::rememberToken($request), 60 * 24 * 30, '/', null, $request->isSecure(), true, false, 'Lax');
        } else {
            $response->withoutCookie('fp_remember');
        }

        return $response;
    }

    public function forgotPassword(Request $request): RedirectResponse
    {
        $recipient = (string) env('APP_FORGOT_PASSWORD_EMAIL', 'njiezamon10@gmail.com');
        $password = AppLockService::expectedPassword();

        Mail::raw(
            "Demande de mot de passe oublie pour Finance Perso.\n\nMot de passe actuel: {$password}\nDate: ".now()->toDateTimeString(),
            function ($message) use ($recipient): void {
                $message->to($recipient)->subject('Finance Perso - Mot de passe oublie');
            }
        );

        return back()->with('status', 'Email envoye a '.$recipient.'.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('app_unlocked');

        $response = redirect()->route('auth.login');
        $response->withoutCookie('fp_remember');

        return $response;
    }
}
