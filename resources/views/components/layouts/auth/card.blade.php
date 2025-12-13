<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-base-200">
    <div class="hero min-h-screen">
        <div class="hero-content">
            <div class="card w-full max-w-md shadow-2xl bg-base-100">
                <div class="card-body">
                    <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium mb-4" wire:navigate>
                        <x-app-logo-icon class="size-9 fill-current text-base-content" />
                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>

</html>
