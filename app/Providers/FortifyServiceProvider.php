<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);

        $this->configureAuthentication();
        $this->configureAuthenticationPipeline();
    }

    /**
     * Configure custom authentication to support both email and username login.
     *
     * Uses the dual authentication system where users can authenticate with either
     * their email address or username. The identifier is extracted using the
     * centralized helper function, and team session is set automatically on success.
     */
    private function configureAuthentication(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
            $identifier = getIdentifierFromRequest($request);
            $password = $request->input('password');

            if (empty($identifier) || empty($password)) {
                return null;
            }

            $user = User::findByIdentifier($identifier)->first();

            if (! $user || ! Hash::check($password, $user->password)) {
                return null;
            }

            setTeamSessionForUser($user);

            return $user;
        });
    }

    /**
     * Configure authentication pipeline.
     *
     * Note: MapLoginIdentifier middleware already handles identifier-to-email mapping
     * before this pipeline runs, so no additional mapping is needed here.
     */
    private function configureAuthenticationPipeline(): void
    {
        Fortify::authenticateThrough(function (Request $request) {
            return $this->buildAuthenticationPipeline($request);
        });
    }

    /**
     * Build authentication pipeline classes.
     *
     * Constructs the pipeline of authentication actions that run during login.
     * Conditionally includes CanonicalizeUsername only when not using identifier field
     * to preserve case sensitivity for usernames.
     *
     * @return array<int, class-string>
     */
    private function buildAuthenticationPipeline(Request $request): array
    {
        return array_filter([
            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            // Skip CanonicalizeUsername when using identifier to preserve case sensitivity for usernames
            (config('fortify.lowercase_usernames') && ! $request->has('identifier')) ? CanonicalizeUsername::class : null,
            Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]);
    }

    /**
     * Configure Fortify views for Livewire.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => $this->getLoginView());
        Fortify::verifyEmailView(fn () => view('pages.auth.verify-email'));
        Fortify::twoFactorChallengeView(fn () => view('pages.auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('pages.auth.confirm-password'));
        Fortify::registerView(fn () => view('pages.auth.register'));
        Fortify::resetPasswordView(fn () => view('pages.auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('pages.auth.forgot-password'));
    }

    /**
     * Get login view with optional user list for development.
     */
    private function getLoginView(): \Illuminate\Contracts\View\View
    {
        $users = isProduction() ? null : $this->getDevelopmentUsers();

        return view('pages.auth.login', compact('users'));
    }

    /**
     * Get users list for development environment login dropdown.
     *
     * @return array<string, string>|null
     */
    private function getDevelopmentUsers(): ?array
    {
        return User::select('username', 'name', 'email')
            ->orderBy('id', 'asc')
            ->get()
            ->mapWithKeys(fn ($user) => [
                $user->username ?? $user->email => $this->formatUserLabel($user),
            ])
            ->toArray();
    }

    /**
     * Format user label for dropdown display.
     */
    private function formatUserLabel(User $user): string
    {
        if ($user->username) {
            return $user->email
                ? "{$user->username} - {$user->name} ({$user->email})"
                : "{$user->username} - {$user->name}";
        }

        return "{$user->email} - {$user->name}";
    }

    /**
     * Configure rate limiting for authentication endpoints.
     *
     * Sets up rate limiters for:
     * - Two-factor authentication: 5 attempts per minute per login session
     * - Login attempts: 5 attempts per minute per identifier/IP combination
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $identifier = getIdentifierFromRequest($request) ?? $request->input(Fortify::username());
            $throttleKey = Str::transliterate(Str::lower($identifier).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
