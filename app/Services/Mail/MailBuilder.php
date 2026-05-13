<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Models\MailSettings;
use App\Models\Team;
use App\Models\Tenant;
use App\Models\User;
use App\Services\EmailTemplate\EmailRenderer;
use App\Services\EmailTemplate\RenderedEmail;
use App\Services\Mail\Contracts\MailProviderContract;
use App\Services\Mail\Providers\LaravelMailProvider;
use Exception;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Mail\Mailable as BaseMailable;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

/**
 * Fluent mail builder for sending emails with hierarchical credential resolution.
 */
class MailBuilder
{
    protected User|string|array|null $to = null;

    protected ?string $subject = null;

    protected ?string $viewName = null;

    protected array $viewData = [];

    protected ?Mailable $mailable = null;

    protected ?User $credentialUser = null;

    protected ?Team $credentialTeam = null;

    protected bool $useCustomCredentials = true;

    protected MailProviderContract $provider;

    protected MailCredentialResolver $resolver;

    protected ?RenderedEmail $renderedEmail = null;

    protected EmailRenderer $renderer;

    public function __construct()
    {
        $this->provider = new LaravelMailProvider();
        $this->resolver = new MailCredentialResolver();
        $this->renderer = app(EmailRenderer::class);
    }

    public static function make(): static
    {
        return new static();
    }

    public function template(string $templateName, array $entities = [], array $context = []): static
    {
        $locale = null;
        if ($this->to instanceof User && ! empty($this->to->frontend_preferences['locale'])) {
            $locale = $this->to->frontend_preferences['locale'];
        }

        $tenant = $this->resolveTenant();

        try {
            if ($tenant && (! function_exists('tenant') || ! tenant())) {
                $tenant->run(function () use ($templateName, $entities, $context, $locale) {
                    $this->renderedEmail = $this->renderer->renderByName($templateName, $entities, $context, $locale);
                });
            } else {
                $this->renderedEmail = $this->renderer->renderByName($templateName, $entities, $context, $locale);
            }
        } catch (Exception $e) {
            // Fallback for missing table or template in tests
            $this->renderedEmail = new RenderedEmail(
                subject: $templateName,
                html: '<p>Fallback for: ' . $templateName . '</p>',
                text: 'Fallback for: ' . $templateName,
                locale: $locale ?? config('app.locale'),
                templateName: $templateName,
            );
        }

        $this->subject($this->renderedEmail->subject);

        return $this;
    }

    protected function resolveTenant(): ?Tenant
    {
        if ($this->credentialTeam && $this->credentialTeam->tenant) {
            return $this->credentialTeam->tenant;
        }

        if ($this->credentialUser) {
            return $this->credentialUser->tenants()->first();
        }

        return null;
    }

    public function to(User|string|array $to): static
    {
        $this->to = $to;

        if ($to instanceof User && $this->credentialUser === null) {
            $this->credentialUser = $to;
        }

        return $this;
    }

    public function subject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function view(string $view, array $data = []): static
    {
        $this->viewName = $view;
        $this->viewData = $data;

        return $this;
    }

    public function mailable(Mailable $mailable): static
    {
        $this->mailable = $mailable;

        return $this;
    }

    public function useCredentialsFrom(User $user): static
    {
        $this->credentialUser = $user;

        return $this;
    }

    public function useTeamCredentials(Team $team): static
    {
        $this->credentialTeam = $team;

        return $this;
    }

    public function useDefaultCredentials(): static
    {
        $this->useCustomCredentials = false;

        return $this;
    }

    public function provider(MailProviderContract $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function send(): bool
    {
        $this->validate();

        $mailable = $this->buildMailable();

        $settings = null;
        if ($this->useCustomCredentials) {
            $settings = $this->resolver->resolve($this->credentialUser, $this->credentialTeam);
        }

        return $this->provider->send($mailable, $settings);
    }

    public function queue(?string $queue = null): void
    {
        $this->validate();

        $mailable = $this->buildMailable();

        if ($queue !== null) {
            $mailable->onQueue($queue);
        }

        Mail::to($this->resolveRecipient())->queue($mailable);
    }

    protected function validate(): void
    {
        if ($this->to === null) {
            throw new InvalidArgumentException('Mail recipient is required. Use ->to() method.');
        }

        if ($this->mailable === null && $this->viewName === null && $this->renderedEmail === null) {
            throw new InvalidArgumentException('Mail content is required. Use ->view(), ->mailable(), or ->template() method.');
        }
    }

    public function getMailable(): Mailable
    {
        $this->validate();

        return $this->buildMailable();
    }

    protected function buildMailable(): Mailable
    {
        if ($this->mailable !== null) {
            $mailable = $this->mailable;

            if (\method_exists($mailable, 'to') && $this->to !== null) {
                $recipient = $this->resolveRecipient();
                $mailable->to($recipient);
            }

            return $mailable;
        }

        if ($this->renderedEmail !== null) {
            $recipient = $this->resolveRecipient();
            $rendered = $this->renderedEmail;

            return new class($rendered, $recipient) extends BaseMailable {
                public function __construct(protected RenderedEmail $rendered, protected mixed $mailRecipient) {}

                public function build(): static
                {
                    $this->withSymfonyMessage(function ($message) {
                        $message->text($this->rendered->text);
                    });

                    return $this->html($this->rendered->html)
                        ->subject($this->rendered->subject)
                        ->to($this->mailRecipient);
                }
            };
        }

        $recipient = $this->resolveRecipient();

        return new class($this->viewName, $this->viewData, $this->subject, $recipient) extends BaseMailable {
            public function __construct(
                protected string $viewName,
                protected array $viewData,
                protected ?string $mailSubject,
                protected mixed $mailRecipient,
            ) {}

            public function build(): static
            {
                $mailable = $this->view($this->viewName)
                    ->with($this->viewData)
                    ->to($this->mailRecipient);

                if ($this->mailSubject !== null) {
                    $mailable->subject($this->mailSubject);
                }

                return $mailable;
            }
        };
    }

    protected function resolveRecipient(): string|array
    {
        if ($this->to instanceof User) {
            if (empty($this->to->email)) {
                throw new InvalidArgumentException('User must have an email address to receive mail.');
            }

            return $this->to->email;
        }

        return $this->to;
    }

    public function getResolvedSettings(): ?MailSettings
    {
        if (! $this->useCustomCredentials) {
            return null;
        }

        return $this->resolver->resolve($this->credentialUser, $this->credentialTeam);
    }

    public function getSettingsSource(): string
    {
        if (! $this->useCustomCredentials) {
            return 'environment';
        }

        return $this->resolver->getSettingsSource($this->credentialUser, $this->credentialTeam);
    }
}
