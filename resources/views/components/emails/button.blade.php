@props(['url'])

<a href="{{ $url }}"
   class="button">
    {{ $slot }}
</a>
