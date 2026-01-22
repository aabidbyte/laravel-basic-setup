@php
    $name = 'Verify Email';
    $description = 'Email sent to users to verify their email address.';
    $entity_types = ['user'];
    $context_variables = ['verification_url'];
    $layout = 'system';
    $subject = __('emails.verify_email.subject');
@endphp

<h1>{{ $subject }}</h1>

<p>
    {{ __('emails.verify_email.intro') }}
</p>

<x-emails.button :url="$verification_url">
    {{ __('emails.verify_email.action') }}
</x-emails.button>

<p>
    {{ __('emails.verify_email.fallback') }}
</p>
