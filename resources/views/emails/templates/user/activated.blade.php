@php
    $name = 'User Activated';
    $description = 'Notification sent to admins/creator when a user activates their account.';
    $entity_types = ['user', 'activated_user'];
    $context_variables = ['action_url'];
    $layout = 'default';
    $subject = __('emails.user_activated.subject', ['name' => '{activated_user.name}']);
@endphp

<h1>{{ $subject }}</h1>

<p>
    {{ __('emails.user_activated.greeting') }}
</p>

<p>
    {{ __('emails.user_activated.line1', ['name' => '{activated_user.name}']) }}
</p>

<p>
    {{ __('emails.user_activated.line2') }}
</p>

<x-emails.button :url="$action_url">
    {{ __('emails.user_activated.action') }}
</x-emails.button>

<p>
    {{ __('emails.user_activated.salutation', ['app' => config('app.name')]) }}
</p>
