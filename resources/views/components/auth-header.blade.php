{{--
    Auth Header Component Props:
    - title: Page title (required)
    - description: Page description (required)
--}}
@props(['title', 'description'])

<div class="flex w-full flex-col text-center">
    <x-ui.title
        level="1"
        class="text-base-content"
    >{{ $title }}</x-ui.title>
    <p class="mt-2 text-sm text-base-content/70">{{ $description }}</p>
</div>
