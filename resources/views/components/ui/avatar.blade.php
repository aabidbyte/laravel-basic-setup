{{--
    Avatar Component Props:
    - user: User model instance (will extract initials and avatar_url if available)
    - name: Fallback name for initials (used if user not provided)
    - src: Image URL (overrides user->avatar_url)
    - size: 'xs', 'sm', 'md', 'lg', 'xl' (default: 'md')
    - shape: 'circle', 'square' (default: 'circle')
    - class: Additional classes
    - placeholder: Show placeholder styling when no image (default: true)
--}}
@props([
    'user' => null,
    'name' => null,
    'src' => null,
    'size' => 'md',
    'shape' => 'circle',
    'class' => '',
    'placeholder' => true,
])

@php
    // Determine image source
    $imageSrc = $src ?? ($user?->avatar_url ?? null);

    // Determine name for initials
    $displayName = $name ?? ($user?->name ?? null);

    // Generate initials
    $initials = '';
    if ($displayName) {
        $initials = method_exists($user ?? new stdClass(), 'initials')
            ? $user->initials()
            : collect(explode(' ', $displayName))->map(fn($word) => strtoupper(substr($word, 0, 1)))->take(2)->join('');
    }

    // Size classes
    $sizeClasses = [
        'xs' => 'w-6 h-6 text-xs',
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-10 h-10 text-base',
        'lg' => 'w-16 h-16 text-xl',
        'xl' => 'w-24 h-24 text-2xl',
    ];

    $shapeClasses = [
        'circle' => 'rounded-full',
        'square' => 'rounded-lg',
    ];

    $containerSize = $sizeClasses[$size] ?? $sizeClasses['md'];
    $shapeClass = $shapeClasses[$shape] ?? $shapeClasses['circle'];
@endphp

<div class="avatar {{ $placeholder && !$imageSrc ? 'placeholder' : '' }} {{ $class }}">
    <div
        class="{{ $containerSize }} {{ $shapeClass }} {{ $imageSrc ? '' : 'bg-primary text-primary-content' }} flex items-center justify-center">
        @if ($imageSrc)
            <img
                src="{{ $imageSrc }}"
                alt="{{ $displayName ?? 'Avatar' }}"
                class="{{ $shapeClass }} object-cover w-full h-full"
            />
        @else
            <span>{{ $initials }}</span>
        @endif
    </div>
</div>
