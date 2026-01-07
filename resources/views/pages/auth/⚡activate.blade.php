<?php

use App\Livewire\Bases\BasePageComponent;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Users\ActivationService;
use Illuminate\Validation\Rules\Password;

new class extends BasePageComponent {
    public ?string $token = null;
    public ?User $user = null;
    public bool $tokenValid = false;
    public bool $activated = false;

    public ?string $pageTitle = 'authentication.activation.title';

    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $activationService = app(ActivationService::class);
        $this->user = $activationService->findUserByToken($token);

        $this->tokenValid = $this->user !== null;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ];
    }

    /**
     * Activate the account.
     */
    public function activateAccount(): void
    {
        if (!$this->tokenValid || !$this->user) {
            NotificationBuilder::make()->title('authentication.activation.invalid_token')->error()->send();
            return;
        }

        $this->validate();

        try {
            $activationService = app(ActivationService::class);
            $activationService->activateWithPassword($this->user, $this->password, $this->token);

            $this->activated = true;

            NotificationBuilder::make()->title('authentication.activation.success')->success()->send();
        } catch (\Exception $e) {
            NotificationBuilder::make()->title('authentication.activation.error')->content($e->getMessage())->error()->send();
        }
    }
}; ?>

<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="card bg-base-100 shadow-xl w-full max-w-md">
        <div class="card-body">
            @if ($activated)
                {{-- Success state --}}
                <div class="text-center space-y-4">
                    <div class="w-16 h-16 mx-auto bg-success/20 rounded-full flex items-center justify-center">
                        <x-ui.icon
                            name="check"
                            class="w-8 h-8 text-success"
                        ></x-ui.icon>
                    </div>
                    <x-ui.title
                        level="2"
                        class="justify-center"
                    >{{ __('authentication.activation.success_title') }}</x-ui.title>
                    <p class="text-base-content/70">{{ __('authentication.activation.success_message') }}</p>
                    <x-ui.button
                        href="{{ route('login') }}"
                        variant="primary"
                        class="w-full"
                    >
                        {{ __('authentication.activation.login_button') }}
                    </x-ui.button>
                </div>
            @elseif ($tokenValid && $user)
                {{-- Activation form --}}
                <div class="text-center mb-6">
                    <x-ui.title
                        level="2"
                        class="justify-center"
                    >{{ __('authentication.activation.title') }}</x-ui.title>
                    <p class="text-base-content/70 mt-2">
                        {{ __('authentication.activation.welcome', ['name' => $user->name]) }}
                    </p>
                </div>

                <x-ui.form
                    wire:submit="activateAccount"
                    class="space-y-4"
                >
                    <x-ui.input
                        type="password"
                        wire:model="password"
                        name="password"
                        :label="__('authentication.activation.password_label')"
                        required
                        autofocus
                        autocomplete="new-password"
                    ></x-ui.input>

                    <x-ui.input
                        type="password"
                        wire:model="password_confirmation"
                        name="password_confirmation"
                        :label="__('authentication.activation.password_confirmation_label')"
                        required
                        autocomplete="new-password"
                    ></x-ui.input>

                    <div class="pt-4">
                        <x-ui.button
                            type="submit"
                            variant="primary"
                            class="w-full"
                        >
                            <x-ui.loading
                                wire:loading
                                wire:target="activateAccount"
                                size="sm"
                            ></x-ui.loading>
                            {{ __('authentication.activation.submit') }}
                        </x-ui.button>
                    </div>
                </x-ui.form>
            @else
                {{-- Invalid/expired token --}}
                <div class="text-center space-y-4">
                    <div class="w-16 h-16 mx-auto bg-error/20 rounded-full flex items-center justify-center">
                        <x-ui.icon
                            name="x-mark"
                            class="w-8 h-8 text-error"
                        ></x-ui.icon>
                    </div>
                    <x-ui.title
                        level="2"
                        class="justify-center"
                    >{{ __('authentication.activation.invalid_title') }}</x-ui.title>
                    <p class="text-base-content/70">{{ __('authentication.activation.invalid_message') }}</p>
                    <x-ui.button
                        href="{{ route('login') }}"
                        variant="ghost"
                    >
                        {{ __('authentication.activation.back_to_login') }}
                    </x-ui.button>
                </div>
            @endif
        </div>
    </div>
</div>
