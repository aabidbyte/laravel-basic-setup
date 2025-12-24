## Layout Configuration

By default, Livewire looks for a layout at `resources/views/layouts/app.blade.php`. You can create this file by running:

```bash
php artisan livewire:layout
```

The layout should include `@livewireStyles` in the `<head>` and `@livewireScripts` before `</body>`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body>
        {{ $slot }}

        @livewireScripts
    </body>
</html>
```

