@props([
    'label' => null,
    'name' => null,
    'options' => [],
    'required' => false,
    'error' => null,
    'placeholder' => null,
])

@php
    $fieldName = $name ?? $attributes->wire('model')->value();
@endphp

<x-ui.select :label="$label"
             :name="$fieldName"
             :options="$options"
             :required="$required"
             :error="$error"
             :placeholder="$placeholder"
             :searchable="false"
             :show-swatches="true"
             {{ $attributes }} />
