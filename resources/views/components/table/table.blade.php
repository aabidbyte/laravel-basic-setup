@props([
    'class' => '',
])

<div class="overflow-x-auto {{ $class }}">
    <table class="table">
        {{ $slot }}
    </table>
</div>

