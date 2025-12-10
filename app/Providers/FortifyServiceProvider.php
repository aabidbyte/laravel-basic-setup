<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
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
    }

    /**
     * Configure Fortify views based on installed frontend stack.
     */
    private function configureViews(): void
    {
        // Check which frontend stack is installed
        $isLivewire = class_exists(\Livewire\Volt\Volt::class);
        $isInertia = class_exists(\Inertia\Inertia::class);

        if ($isLivewire) {
            // Use Livewire views
            Fortify::loginView(fn () => view('livewire.auth.login'));
            Fortify::verifyEmailView(fn () => view('livewire.auth.verify-email'));
            Fortify::twoFactorChallengeView(fn () => view('livewire.auth.two-factor-challenge'));
            Fortify::confirmPasswordView(fn () => view('livewire.auth.confirm-password'));
            Fortify::registerView(fn () => view('livewire.auth.register'));
            Fortify::resetPasswordView(fn () => view('livewire.auth.reset-password'));
            Fortify::requestPasswordResetLinkView(fn () => view('livewire.auth.forgot-password'));
        } elseif ($isInertia && class_exists(\Inertia\Inertia::class)) {
            // Use Inertia responses for React/Vue
            Fortify::loginView(fn () => \Inertia\Inertia::render('Auth/Login'));
            Fortify::verifyEmailView(fn () => \Inertia\Inertia::render('Auth/VerifyEmail'));
            Fortify::twoFactorChallengeView(fn () => \Inertia\Inertia::render('Auth/TwoFactorChallenge'));
            Fortify::confirmPasswordView(fn () => \Inertia\Inertia::render('Auth/ConfirmPassword'));
            Fortify::registerView(fn () => \Inertia\Inertia::render('Auth/Register'));
            Fortify::resetPasswordView(fn () => \Inertia\Inertia::render('Auth/ResetPassword'));
            Fortify::requestPasswordResetLinkView(fn () => \Inertia\Inertia::render('Auth/ForgotPassword'));
        } else {
            // Fallback to simple Blade views if neither is installed
            Fortify::loginView(fn () => view('auth.login'));
            Fortify::verifyEmailView(fn () => view('auth.verify-email'));
            Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));
            Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));
            Fortify::registerView(fn () => view('auth.register'));
            Fortify::resetPasswordView(fn () => view('auth.reset-password'));
            Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        }
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
