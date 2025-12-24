## Password

**Location:** `resources/views/components/ui/password.blade.php`

**Component Name:** `<x-ui.password>`

### Description

A specialized password input component with built-in show/hide toggle functionality. Includes an eye icon button that toggles password visibility. Uses Alpine.js for state management and follows the same patterns as the input component.

### Props

| Prop       | Type           | Default | Description                                                             |
| ---------- | -------------- | ------- | ----------------------------------------------------------------------- |
| `label`    | `string\|null` | `null`  | Optional label text displayed above the input                           |
| `error`    | `string\|null` | `null`  | Optional error message to display (overrides automatic error detection) |
| `required` | `bool`         | `false` | Whether to show a red asterisk (\*) indicating the field is required    |

### Additional Attributes

All standard HTML input attributes are supported (e.g., `name`, `id`, `placeholder`, `wire:model`, etc.). The `type` attribute is automatically set to `password` and should not be overridden.

### Usage Examples

#### Basic Password Input

```blade
<x-ui.password name="password" label="Password" />
```

#### Required Password Input

```blade
<x-ui.password name="password" label="Password" :required="true" />
```

#### Password Input with Livewire

```blade
<x-ui.password wire:model="password" name="password" label="Password" />
```

#### Password Input with Manual Error

```blade
<x-ui.password name="password" label="Password" error="Password is required" />
```

#### Password Input with Automatic Error Detection

The component automatically detects errors from Laravel's `$errors` bag based on the input's `name` attribute:

```blade
{{-- If validation fails for 'password' field, error will be shown automatically --}}
<x-ui.password name="password" label="Password" />
```

#### Password Input with Label Append (e.g., Forgot Password Link)

```blade
<x-ui.password name="password" label="Password">
    <x-slot:label-append>
        <a href="{{ route('password.request') }}" class="label-text-alt link">
            Forgot password?
        </a>
    </x-slot:label-append>
</x-ui.password>
```

#### Password Confirmation

```blade
<x-ui.password name="password" label="Password" />
<x-ui.password name="password_confirmation" label="Confirm Password" />
```

### Slots

-   **Default slot:** Not used (input is self-closing)
-   **`label-append` slot:** Optional content to display on the right side of the label (e.g., "Forgot password?" link)

### Implementation Details

-   Uses Alpine.js for show/hide toggle functionality (`x-data`, `x-bind:type`, `x-show`)
-   Automatically generates a unique ID if not provided
-   Automatically detects validation errors from Laravel's `$errors` bag
-   Applies `input-error` class when errors are present
-   Uses DaisyUI form control and label classes
-   Icon button positioned absolutely on the right side of the input
-   Eye icon (`eye`) shown when password is hidden
-   Eye-slash icon (`eye-slash`) shown when password is visible
-   Icon button includes proper ARIA labels for accessibility
-   Supports all HTML input attributes except `type` (automatically set to `password`)
-   Supports `label-append` slot for additional label content
-   Input has `pr-10` padding to accommodate the icon button

### Accessibility

-   Icon button includes `aria-label` that changes based on visibility state
-   Icon button is keyboard accessible (tabindex="0")
-   Uses semantic HTML structure
-   Icons use `x-cloak` to prevent flash of unstyled content

### Current Usage in Project

The password component can be used anywhere a password input is needed, including:

-   Login forms
-   Registration forms
-   Password reset forms
-   Password update forms
-   Any form requiring password input

---

