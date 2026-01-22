@php
    $name = 'Security Email Change';
    $description = 'Notification sent to old email when an email change is requested';
    $entity_types = ['user'];
    $context_variables = ['new_email', 'support_email'];
    $layout = 'system';
    $subject = __('emails.email_change_security.subject');
@endphp

<h1>{{ $subject }}</h1>

<div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 5px; padding: 15px; margin: 20px 0;">
    <p style="color: #991b1b; margin: 0; font-weight: bold;">
        ⚠️ {{ __('emails.email_change_security.warning_title') }}
    </p>
</div>

<p>{{ __('emails.email_change_security.intro') }}</p>

<p style="font-weight: bold;">
    {{ __('emails.email_change_security.new_email') }}: {{ $new_email }}
</p>

<p>{{ __('emails.email_change_security.if_not_you') }}</p>

<div style="background-color: #f3f4f6; border-radius: 5px; padding: 15px; margin: 20px 0;">
    <p style="margin: 0; color: #374151;">
        {{ __('emails.email_change_security.contact_support') }}
        <a href="mailto:{{ $support_email }}"
           style="color: #667eea;">{{ $support_email }}</a>
    </p>
</div>
