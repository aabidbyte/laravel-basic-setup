@props(['status'])

@if ($status)
    <div class="alert alert-success" {{ $attributes }}>
        <span>{{ $status }}</span>
    </div>
@endif
