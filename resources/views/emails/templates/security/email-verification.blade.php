@php
    $name = 'Email Change Verification';
    $description = 'Verification email sent to the new email address';
    $entity_types = ['user'];
    $context_variables = ['action_url'];
    $layout = 'system';
    $subject = __('emails.email_change_verification.subject');
@endphp

<h1>{{ __('emails.email_change_verification.greeting', ['name' => $user->name]) }}</h1>

<p>{{ __('emails.email_change_verification.intro') }}</p>

<x-emails.button :url="$action_url">
    {{ __('emails.email_change_verification.button') }}
</x-emails.button>

<p>{{ __('emails.email_change_verification.expiry') }}</p>
