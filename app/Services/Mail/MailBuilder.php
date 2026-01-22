<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Models\MailSettings;
use App\Models\Team;
use App\Models\User;
use App\Services\EmailTemplate\EmailRenderer;
use App\Services\EmailTemplate\RenderedEmail;
use App\Services\Mail\Contracts\MailProviderContract;
use App\Services\Mail\Providers\LaravelMailProvider;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Mail\Mailable as BaseMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;

/**
 * Fluent mail builder for sending emails with hierarchical credential resolution.
 *
 * Similar to NotificationBuilder, provides a fluent API for building and sending emails.
 *
 * Credential resolution order (with CONFIGURE_MAIL_SETTINGS permission check):
 * 1. User settings (if user has permission)
 * 2. Team settings
 * 3. App settings
 * 4. Environment variables
 *
 * @example
 * ```php
 * MailBuilder::make()
 *     ->to($user)
 *     ->subject('Account Activation')
 *     ->view('emails.activation', ['link' => $link])
 *     ->send();
 * ```
 */
class MailBuilder
{
    /**
     * @var User|string|array<string>|null Recipients (User, email string, or array)
     */
    protected User|string|array|null $to = null;

    /**
     * @var string|null Email subject
     */
    protected ?string $subject = null;

    /**
     * @var string|null View name for the email body
     */
    protected ?string $viewName = null;

    /**
     * @var array<string, mixed> View data
     */
    protected array $viewData = [];

    /**
     * @var Mailable|null Pre-built mailable
     */
    protected ?Mailable $mailable = null;

    /**
     * @var User|null User to use for credential resolution
     */
    protected ?User $credentialUser = null;

    /**
     * @var Team|null Team to use for credential resolution
     */
    protected ?Team $credentialTeam = null;

    /**
     * @var bool Whether to use custom credentials
     */
    protected bool $useCustomCredentials = true;

    /**
     * @var MailProviderContract The mail provider
     */
    protected MailProviderContract $provider;

    /**
     * @var MailCredentialResolver The credential resolver
     */
    protected MailCredentialResolver $resolver;

    /**
     * Create a new mail builder instance.
     */
    /**
     * @var RenderedEmail|null Pre-rendered email content
     */
    protected ?RenderedEmail $renderedEmail = null;

    protected EmailRenderer $renderer;

    /**
     * Create a new mail builder instance.
     */
    public function __construct()
    {
        $this->provider = new LaravelMailProvider;
        $this->resolver = new MailCredentialResolver;
        $this->renderer = app(EmailRenderer::class);
    }

    /**
     * Create a new mail builder instance (factory method).
     *
     * @return static A new mail builder instance
     */
    public static function make(): static
    {
        return new static;
    }

    /**
     * Set the email content from a Database Template.
     *
     * @param  string  $templateName  The name of the template (e.g. 'User Welcome')
     * @param  array<string, mixed>  $entities  Entities for merge tags (e.g. ['user' => $user])
     * @param  array<string, mixed>  $context  Context variables (e.g. ['action_url' => '...'])
     */
    public function template(string $templateName, array $entities = [], array $context = []): static
    {
        // Resolve target locale from recipient if possible, otherwise use app locale
        $locale = null;
        if ($this->to instanceof User && ! empty($this->to->frontend_preferences['locale'])) {
            $locale = $this->to->frontend_preferences['locale'];
        }

        $this->renderedEmail = $this->renderer->renderByName($templateName, $entities, $context, $locale);

        $this->subject($this->renderedEmail->subject);

        return $this;
    }

    /**
     * Set the recipient(s).
     *
     * @param  User|string|array<string>  $to  The recipient(s)
     */
    public function to(User|string|array $to): static
    {
        $this->to = $to;

        // If recipient is a User, also use them for credential resolution by default
        if ($to instanceof User && $this->credentialUser === null) {
            $this->credentialUser = $to;
        }

        return $this;
    }

    /**
     * Set the email subject.
     *
     * @param  string  $subject  The email subject
     */
    public function subject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set the email body from a Blade view.
     *
     * @param  string  $view  The view name
     * @param  array<string, mixed>  $data  Data to pass to the view
     */
    public function view(string $view, array $data = []): static
    {
        $this->viewName = $view;
        $this->viewData = $data;

        return $this;
    }

    /**
     * Use a pre-built Mailable instance.
     *
     * @param  Mailable  $mailable  The mailable to send
     */
    public function mailable(Mailable $mailable): static
    {
        $this->mailable = $mailable;

        return $this;
    }

    /**
     * Specify the user to use for credential resolution.
     *
     * @param  User  $user  The user whose credentials to use
     */
    public function useCredentialsFrom(User $user): static
    {
        $this->credentialUser = $user;

        return $this;
    }

    /**
     * Specify the team to use for credential resolution.
     *
     * @param  Team  $team  The team whose credentials to use
     */
    public function useTeamCredentials(Team $team): static
    {
        $this->credentialTeam = $team;

        return $this;
    }

    /**
     * Use only environment/default credentials (skip custom settings).
     */
    public function useDefaultCredentials(): static
    {
        $this->useCustomCredentials = false;

        return $this;
    }

    /**
     * Set a custom mail provider.
     *
     * @param  MailProviderContract  $provider  The mail provider to use
     */
    public function provider(MailProviderContract $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Send the email.
     *
     * @return bool True if the email was sent successfully
     *
     * @throws InvalidArgumentException If required fields are missing
     */
    public function send(): bool
    {
        $this->validate();

        // Get the mailable
        $mailable = $this->buildMailable();

        // Resolve credentials
        $settings = null;
        if ($this->useCustomCredentials) {
            $settings = $this->resolver->resolve($this->credentialUser, $this->credentialTeam);
        }

        // Send using the provider
        return $this->provider->send($mailable, $settings);
    }

    /**
     * Queue the email for later sending.
     *
     * @param  string|null  $queue  The queue name (optional)
     */
    public function queue(?string $queue = null): void
    {
        $this->validate();

        $mailable = $this->buildMailable();

        // Resolve credentials
        $settings = null;
        if ($this->useCustomCredentials) {
            $settings = $this->resolver->resolve($this->credentialUser, $this->credentialTeam);
        }

        // For queued mail, we configure the mailer in the Mailable itself
        // This is handled by the mailable class
        if ($queue !== null) {
            $mailable->onQueue($queue);
        }

        Mail::to($this->resolveRecipient())->queue($mailable);
    }

    /**
     * Validate required fields before sending.
     *
     * @throws InvalidArgumentException If required fields are missing
     */
    protected function validate(): void
    {
        if ($this->to === null) {
            throw new InvalidArgumentException('Mail recipient is required. Use ->to() method.');
        }

        if ($this->mailable === null && $this->viewName === null && $this->renderedEmail === null) {
            throw new InvalidArgumentException('Mail content is required. Use ->view(), ->mailable(), or ->template() method.');
        }
    }

    /**
     * Get the built mailable instance (without sending).
     */
    public function getMailable(): Mailable
    {
        $this->validate();

        return $this->buildMailable();
    }

    /**
     * Build the mailable instance.
     *
     * @return Mailable The mailable instance
     */
    protected function buildMailable(): Mailable
    {
        // If a pre-built mailable was provided, use it
        if ($this->mailable !== null) {
            $mailable = $this->mailable;

            // Set recipient if not already set on mailable
            if (method_exists($mailable, 'to') && $this->to !== null) {
                $recipient = $this->resolveRecipient();
                $mailable->to($recipient);
            }

            return $mailable;
        }

        // Build from RenderedEmail (Database Template)
        if ($this->renderedEmail !== null) {
            $recipient = $this->resolveRecipient();
            $rendered = $this->renderedEmail;

            return new class($rendered, $recipient) extends BaseMailable
            {
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

        // Build a simple mailable from view
        $recipient = $this->resolveRecipient();

        return new class($this->viewName, $this->viewData, $this->subject, $recipient) extends BaseMailable
        {
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

    /**
     * Resolve the recipient to an email address or array.
     *
     * @return string|array<string> The recipient email(s)
     */
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

    /**
     * Get the resolved mail settings (for debugging/testing).
     *
     * @return MailSettings|null The resolved settings
     */
    public function getResolvedSettings(): ?MailSettings
    {
        if (! $this->useCustomCredentials) {
            return null;
        }

        return $this->resolver->resolve($this->credentialUser, $this->credentialTeam);
    }

    /**
     * Get the settings source (for debugging/logging).
     *
     * @return string The source name ('user', 'team', 'app', 'environment')
     */
    public function getSettingsSource(): string
    {
        if (! $this->useCustomCredentials) {
            return 'environment';
        }

        return $this->resolver->getSettingsSource($this->credentialUser, $this->credentialTeam);
    }
}
