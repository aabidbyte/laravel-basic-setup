# wire:ignore

`wire:ignore` tells Livewire to ignore an element and its children during subsequent renders.

## Basic Usage

```blade
<div wire:ignore>
    <!-- Use a 3rd party JS library here (like ChartJS or Select2) -->
    <canvas id="myChart"></canvas>
</div>
```

## Self-Updating Elements

Use `wire:ignore.self` to ignore the element's own attributes but still allow Livewire to update its children.

## Why use wire:ignore?

-   Integrating with **3rd party libraries** that manipulate the DOM (to prevent Livewire from undoing their changes).
-   **Performance**: Preventing deep morphing on parts of the page that never change or are managed entirely by Alpine.
