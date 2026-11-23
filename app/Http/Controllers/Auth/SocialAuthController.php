<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'discord', 'x'];

    public function redirect(Request $request, string $provider)
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        if (blank(config("services.{$provider}.client_id")) || blank(config("services.{$provider}.client_secret"))) {
            return redirect()->route('login')->withErrors([
                'social' => ucfirst($provider).' sign in is not configured yet. Add its OAuth credentials to the server environment.',
            ]);
        }

        $intended = $request->string('intended')->toString();
        if ($this->isSafeIntendedPath($intended)) {
            $request->session()->put('url.intended', $intended);
        }

        $driver = Socialite::driver($provider);

        return $provider === 'discord'
            ? $driver->withConsent()->redirect()
            : $driver->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'social' => 'Sign in was cancelled or could not be completed. Please try again.',
            ]);
        }

        $account = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', (string) $socialUser->getId())
            ->first();

        if ($account && Auth::check() && $account->user_id !== Auth::id()) {
            return redirect()->route('settings.account')->withErrors([
                'social' => 'This social account is already connected to another Curator account.',
            ]);
        }

        if (Auth::check()) {
            $alreadyLinked = SocialAccount::query()
                ->where('user_id', Auth::id())
                ->where('provider', $provider)
                ->where('provider_user_id', '!=', (string) $socialUser->getId())
                ->exists();

            if ($alreadyLinked) {
                return redirect()->route('settings.account')->withErrors([
                    'social' => 'A different account from this provider is already connected.',
                ]);
            }
        }

        $user = DB::transaction(function () use ($request, $provider, $socialUser, $account) {
            if ($account) {
                return $account->user;
            }

            $user = $request->user();
            if (! $user) {
                $providerEmail = $socialUser->getEmail();
                $verifiedEmail = $this->hasVerifiedEmail($provider, $socialUser);
                if ($verifiedEmail && $providerEmail) {
                    $user = User::query()->where('email', $providerEmail)->first();
                }

                if ($user) {
                    return $this->attachSocialAccount($user, $provider, $socialUser);
                }

                $user = User::create([
                    'name' => $socialUser->getName()
                        ?: $socialUser->getNickname()
                        ?: ucfirst($provider).' player',
                    'email' => $verifiedEmail && $providerEmail
                        ? $providerEmail
                        : $provider.'_'.hash('sha256', (string) $socialUser->getId()).'@oauth.curator.local',
                    'email_verified_at' => $verifiedEmail && $providerEmail ? now() : null,
                    'password' => Str::random(48),
                    'steam_avatar_url' => $socialUser->getAvatar(),
                ]);

                if ($verifiedEmail && $providerEmail) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }
            }

            return $this->attachSocialAccount($user, $provider, $socialUser);
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/plebiscite');
    }

    private function isSafeIntendedPath(string $path): bool
    {
        return str_starts_with($path, '/')
            && ! str_starts_with($path, '//')
            && ! str_starts_with($path, '/\\')
            && ! str_contains($path, '\\')
            && ! preg_match('/[\r\n]/', $path);
    }

    private function hasVerifiedEmail(string $provider, object $socialUser): bool
    {
        $profile = (array) ($socialUser->user ?? []);

        return match ($provider) {
            'google' => (bool) ($profile['email_verified'] ?? $profile['verified_email'] ?? false),
            'discord' => (bool) ($profile['verified'] ?? false),
            default => false,
        };
    }

    private function attachSocialAccount(User $user, string $provider, object $socialUser): User
    {
        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_user_id' => (string) $socialUser->getId(),
            'provider_email' => $socialUser->getEmail(),
        ]);

        return $user;
    }
}
