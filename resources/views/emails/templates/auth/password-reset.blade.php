@php
    $name = 'Password Reset';
    $description = 'Email sent to users to reset their password.';
    $entity_types = ['user'];
    $context_variables = ['reset_url', 'count'];
    $layout = 'system';
    $subject = __('emails.password_reset.subject');
@endphp

<h1>{{ $subject }}</h1>

<p>
    {{ __('emails.password_reset.intro') }}
</p>

<x-emails.button :url="$reset_url">
    {{ __('emails.password_reset.action') }}
</x-emails.button>

<p>
    {{ __('emails.password_reset.expiry', ['count' => $count]) }}
</p>

<p>
    {{ __('emails.password_reset.fallback') }}
</p>
