# wire:current

`wire:current` is a utility modifier used mostly on nav links to automatically apply an "active" class based on the current URL.

## Basic Usage

```blade
<a href="/dashboard" 
   wire:navigate
   wire:current="bg-blue-500 font-bold">
    Dashboard
</a>
```

If the current URL matches `/dashboard`, the classes will be appended to the element automatically.

## Partial Matches

You can use standard Wildcards or specific logic if needed, though most nav scenarios are handled by route matching.
