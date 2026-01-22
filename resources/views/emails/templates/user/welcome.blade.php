@php
    $name = 'User Welcome';
    $description = 'Welcome email sent to new users.';
    $entity_types = ['user'];
    $context_variables = ['action_url', 'login_url'];
    $layout = 'default';
    $subject = __('emails.user_welcome.subject');
@endphp

<h1>{{ __('emails.user_welcome.greeting', ['name' => $user->name]) }}</h1>

<p>
    {{ __('emails.user_welcome.intro') }}
</p>

<x-emails.button :url="$action_url">
    {{ __('emails.user_welcome.action') }}
</x-emails.button>

<p>
    {{ __('emails.user_welcome.closing') }}
</p>
