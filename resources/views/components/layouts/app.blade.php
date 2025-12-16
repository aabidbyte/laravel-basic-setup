<!DOCTYPE html>
<html lang="{{ $i18n->getHtmlLangAttribute() }}" dir="{{ $i18n->getHtmlDirAttribute() }}">

<head>
    @include('partials.head')

    @livewireStyles
</head>

<body>
    <x-layouts.app.sidebar :title="$title ?? null">
        <main class="flex-1">
            {{ $slot }}
        </main>
    </x-layouts.app.sidebar>

    @livewireScripts
</body>

</html>
