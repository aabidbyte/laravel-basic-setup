<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport"
              content="width=device-width, initial-scale=1">
        <title>{{ __('emails.email_change_security.subject', ['app' => $appName]) }}</title>
    </head>

    <body
          style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
        <div
             style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;">
            <h1 style="color: white; margin: 0; font-size: 24px;">{{ $appName }}</h1>
        </div>

        <div
             style="background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;">
            <h2 style="color: #333; margin-top: 0;">
                {{ __('emails.email_change_security.greeting', ['name' => $user->name]) }}</h2>

            <div
                 style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 5px; padding: 15px; margin: 20px 0;">
                <p style="color: #991b1b; margin: 0; font-weight: bold;">
                    ⚠️ {{ __('emails.email_change_security.warning_title') }}
                </p>
            </div>

            <p>{{ __('emails.email_change_security.intro') }}</p>

            <p style="font-weight: bold;">
                {{ __('emails.email_change_security.new_email') }}: {{ $newEmail }}
            </p>

            <p>{{ __('emails.email_change_security.if_not_you') }}</p>

            <div style="background: #f3f4f6; border-radius: 5px; padding: 15px; margin: 20px 0;">
                <p style="margin: 0; color: #374151;">
                    {{ __('emails.email_change_security.contact_support') }}
                    <a href="mailto:{{ $supportEmail }}"
                       style="color: #667eea;">{{ $supportEmail }}</a>
                </p>
            </div>

            <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;">

            <p style="color: #999; font-size: 12px; text-align: center;">
                {{ __('emails.email_change_security.footer') }}
            </p>
        </div>
    </body>

</html>
