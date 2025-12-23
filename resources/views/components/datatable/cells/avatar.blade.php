@props([
    'value' => null,
    'defaultAvatar' => null,
    'name' => 'User',
])

<div class="avatar">
    <div class="w-10 rounded-full">
        <img src="{{ $value ?? ($defaultAvatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($name)) }}"
            alt="{{ $name }}" />
    </div>
</div>

