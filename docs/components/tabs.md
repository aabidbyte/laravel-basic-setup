# Tabs Component

**Location:** `resources/views/components/ui/tabs.blade.php`

**Component Name:** `<x-ui.tabs>`

## Description

A reusable DaisyUI tabs wrapper for Livewire pages. It renders a tab button for each item and updates a Livewire property with `wire:click`.

## Props

| Prop | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `tabs` | `array` | `[]` | Array of tab definitions. Each item supports `key`, `label`, and optional `icon`. |
| `active` | `string|null` | `null` | The currently active tab key. |
| `target` | `string` | `activeTab` | Livewire property updated when a tab is clicked. |
| `size` | `string` | `lg` | Tab size: `sm`, `md`, `lg`, or `xl`. |
| `style` | `string` | `lifted` | DaisyUI style: `lifted`, `boxed`, or `bordered`. |

## Usage

```blade
<x-ui.tabs :tabs="[
    ['key' => 'overview', 'label' => __('tenancy.overview'), 'icon' => 'information-circle'],
    ['key' => 'users', 'label' => __('tenancy.assigned_users'), 'icon' => 'users'],
]"
:active="$activeTab" />
```

## Show Page Pattern

Use tabs on show/detail pages when the page has more than one meaningful content section, multiple management surfaces, or nested datatables that compete for attention. Keep the first tab as an overview, then split relationship management, assignments, audit/history, and other secondary workflows into separate tabs.

Render tab panels with server-side conditionals (`@if` / `@elseif`) when a panel contains nested Livewire components, and add `lazy` to expensive child components so hidden panels are not mounted on first paint. Avoid CSS-only hiding for inactive tab panels with Livewire children.
