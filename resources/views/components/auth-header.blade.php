@props(['title', 'description'])

<div class="flex w-full flex-col text-center">
    <h1 class="text-2xl font-semibold text-base-content">{{ $title }}</h1>
    <p class="mt-2 text-sm text-base-content/70">{{ $description }}</p>
</div>
