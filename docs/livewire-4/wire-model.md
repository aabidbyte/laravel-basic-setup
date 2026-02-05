# wire:model

`wire:model` provides two-way data binding between a property in your component and an input element.

## Basic Usage

```blade
<input type="text" wire:model="name">
```

## Update Strategies

-   `wire:model.live`: Updates the component state on every input/change event (useful for search).
-   `wire:model.blur`: Updates when the input loses focus.
-   `wire:model`: (Default) Updates on the next server request (usually a click or submit).

## Modifiers

-   `.debounce.500ms`: Wait 500ms after the user stops typing before syncing (Livewire 4 uses intelligent debouncing by default).
-   `.throttle.500ms`: Limits syncing to once every 500ms.

## Binding to Arrays

```blade
<input type="checkbox" wire:model="selected" value="1">
<input type="checkbox" wire:model="selected" value="2">
```

## Nesting and Form Objects

```blade
<input type="text" wire:model="form.title">
```

## Security

Use the `#[Locked]` attribute for properties that should be bound but never modified by the user directly (e.g., specific IDs that shouldn't be manipulated via JS).
