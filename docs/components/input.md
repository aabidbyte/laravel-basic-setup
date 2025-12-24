## Input

**Location:** `resources/views/components/ui/input.blade.php`

**Component Name:** `<x-ui.input>`

### Description

A centralized input component with built-in label, error handling, and DaisyUI styling.

### Props

| Prop       | Type           | Default | Description                                                             |
| ---------- | -------------- | ------- | ----------------------------------------------------------------------- |
| `label`    | `string\|null` | `null`  | Optional label text displayed above the input                           |
| `error`    | `string\|null` | `null`  | Optional error message to display (overrides automatic error detection) |
| `required` | `bool`         | `false` | Whether to show a red asterisk (\*) indicating the field is required    |

### Additional Attributes

All standard HTML input attributes are supported (e.g., `type`, `name`, `id`, `placeholder`, `wire:model`, etc.).

### Usage Examples

#### Basic Input

```blade
<x-ui.input type="text" name="name" label="Name" />
```

#### Required Input

```blade
<x-ui.input type="email" name="email" label="Email" :required="true" />
```

#### Input with Livewire

```blade
<x-ui.input type="text" wire:model="name" name="name" label="Name" />
```

#### Input with Manual Error

```blade
<x-ui.input type="password" name="password" label="Password" error="Password is required" />
```

#### Input with Automatic Error Detection

The component automatically detects errors from Laravel's `$errors` bag based on the input's `name` attribute:

```blade
{{-- If validation fails for 'email' field, error will be shown automatically --}}
<x-ui.input type="email" name="email" label="Email" />
```

#### Input with Label Append (e.g., Forgot Password Link)

```blade
<x-ui.input type="password" name="password" label="Password">
    <x-slot:label-append>
        <a href="{{ route('password.request') }}" class="label-text-alt link">
            Forgot password?
        </a>
    </x-slot:label-append>
</x-ui.input>
```

#### Password Input

```blade
<x-ui.input type="password" wire:model="password" name="password" label="Password" />
```

#### Input with Placeholder

```blade
<x-ui.input type="text" name="search" label="Search" placeholder="Enter search term..." />
```

### Slots

-   **Default slot:** Not used (input is self-closing)
-   **`label-append` slot:** Optional content to display on the right side of the label (e.g., "Forgot password?" link)

### Implementation Details

-   Automatically generates a unique ID if not provided
-   Automatically detects validation errors from Laravel's `$errors` bag
-   Applies `input-error` class when errors are present
-   Uses DaisyUI form control and label classes
-   Supports all HTML input types and attributes
-   Supports `label-append` slot for additional label content

---

