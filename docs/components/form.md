## Form

**Location:** `resources/views/components/ui/form.blade.php`

**Component Name:** `<x-ui.form>`

### Description

A centralized form component that automatically handles CSRF tokens, method spoofing, and form attributes.

### Props

| Prop     | Type           | Default  | Description                                                                  |
| -------- | -------------- | -------- | ---------------------------------------------------------------------------- |
| `method` | `string`       | `'POST'` | HTTP method: `GET`, `POST`, `PUT`, `PATCH`, `DELETE` (automatically spoofed) |
| `action` | `string\|null` | `null`   | Form action URL (optional, defaults to current URL)                          |
| `class`  | `string`       | `''`     | Additional CSS classes for the form                                          |

### Usage Examples

#### Basic Form

```blade
<x-ui.form method="POST" action="{{ route('login.store') }}">
    <x-ui.input type="email" name="email" label="Email" />
    <x-ui.button type="submit">Submit</x-ui.button>
</x-ui.form>
```

#### Livewire Form

```blade
<x-ui.form wire:submit="updateProfile">
    <x-ui.input wire:model="name" name="name" label="Name" />
    <x-ui.button type="submit">Save</x-ui.button>
</x-ui.form>
```

#### Form with Custom Method

```blade
{{-- Automatically adds @method('PUT') and @csrf --}}
<x-ui.form method="PUT" action="{{ route('users.update', $user) }}">
    <x-ui.input wire:model="name" name="name" label="Name" />
    <x-ui.button type="submit">Update</x-ui.button>
</x-ui.form>
```

#### Form with Additional Classes

```blade
{{-- Default spacing (space-y-6) is applied automatically --}}
<x-ui.form method="POST" class="flex flex-col">
    {{-- Form content --}}
</x-ui.form>

{{-- Override spacing if needed --}}
<x-ui.form method="POST" class="space-y-4">
    {{-- Form content with custom spacing --}}
</x-ui.form>
```

### Implementation Details

-   Automatically adds `@csrf` token for non-GET requests
-   Automatically adds `@method()` directive for PUT, PATCH, DELETE requests
-   **Centralized spacing:** Automatically applies `space-y-6` for consistent vertical spacing between form elements
-   Spacing can be overridden by providing custom spacing classes in the `class` prop
-   Merges additional attributes (like `wire:submit`, `class`, `data-*`) using Laravel's attribute merging
-   Supports all standard HTML form attributes

---

