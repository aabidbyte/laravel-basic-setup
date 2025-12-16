<!DOCTYPE html>
<html lang="{{ $i18n->getHtmlLangAttribute() }}" dir="{{ $i18n->getHtmlDirAttribute() }}">

<head>
    @include('partials.head')

    @livewireStyles
</head>

<body>
    <x-layouts.auth.simple :title="$title ?? null">
        {{ $slot }}
    </x-layouts.auth.simple>

    @livewireScripts
</body>

</html>
