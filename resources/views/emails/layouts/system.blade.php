@php
    $name = 'system';
    $display_name = 'System Layout';
    $description = 'Minimal layout for transactional emails.';
    $is_default = false;
@endphp
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport"
              content="width=device-width, initial-scale=1">
        <title>@{{ app.name }}</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f5f5f5;
            }

            .email-wrapper {
                background: #ffffff;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            .email-header {
                background: #333;
                /* Darker header for system emails */
                padding: 20px;
                text-align: center;
            }

            .email-header h1 {
                color: white;
                margin: 0;
                font-size: 20px;
            }

            .email-body {
                padding: 30px;
            }

            .email-footer {
                padding: 20px 30px;
                background: #f9fafb;
                border-top: 1px solid #e0e0e0;
                text-align: center;
            }

            .email-footer p {
                color: #999;
                font-size: 12px;
                margin: 0;
            }

            .btn {
                display: inline-block;
                background: #333;
                color: white;
                text-decoration: none;
                padding: 12px 24px;
                border-radius: 4px;
                font-weight: bold;
                font-size: 14px;
            }

            .btn:hover {
                opacity: 0.9;
            }

            hr {
                border: none;
                border-top: 1px solid #e0e0e0;
                margin: 20px 0;
            }
        </style>
    </head>

    <body>
        <div class="email-wrapper">
            <div class="email-header">
                <h1>@{{ app.name }}</h1>
            </div>

            <div class="email-body">
                {{ $slot }}
            </div>

            <div class="email-footer">
                <p>&copy; @{{ meta.year }} @{{ app.name }}. All rights reserved.</p>
            </div>
        </div>
    </body>

</html>
