@props([
    'method' => 'POST',
    'action' => null,
    'class' => '',
])

@php
    $formMethod = strtoupper($method);
    $isGet = $formMethod === 'GET';
    $isPost = $formMethod === 'POST';
    $needsMethodSpoofing = !$isGet && !$isPost;
    $formAction = $action ? 'action="' . $action . '"' : '';
@endphp

<form method="{{ $isGet ? 'GET' : 'POST' }}"
      {!! $formAction !!}
      x-data="submitForm"
      {{ $attributes->merge(['class' => trim('space-y-6 ' . $class)])->except(['method', 'action']) }}>
    @if ($needsMethodSpoofing)
        @method($formMethod)
    @endif

    @if (!$isGet)
        @csrf
    @endif

    {{ $slot }}
</form>

@assets
    <script>
        (function() {
            const register = () => {
                Alpine.data('submitForm', () => ({
                    init() {
                        this.$el.addEventListener('submit', () => {
                            this.$el.classList.add('form-submitting');
                        });
                    },
                }));
            };

            if (window.Alpine) {
                register();
            } else {
                document.addEventListener('alpine:init', register);
            }
        })();
    </script>
@endassets
