## Base Modal

**Location:** `resources/views/components/ui/base-modal.blade.php`

**Class:** `app/View/Components/Ui/BaseModal.php`

**Component Name:** `<x-ui.base-modal>`

### Description

A comprehensive, flexible modal component built with Alpine.js following Penguin UI patterns. This is the foundation for all modals in the project, providing extensive customization options including transitions, variants, focus trapping, and accessibility features. The base modal uses pure Alpine.js for state management and does not rely on the HTML `<dialog>` element, making it more flexible for complex use cases.

**Note:** This is the primary modal component used throughout the project. All modals should use `<x-ui.base-modal>` directly with Alpine.js state management.

### Props

#### Modal Identification

| Prop      | Type     | Default | Description                                                      |
| --------- | -------- | ------- | ---------------------------------------------------------------- |
| `id`      | `string` | `null`  | Unique ID for the modal (auto-generated if not provided)        |
| `modalId` | `string` | `null`  | Alternative prop name for modal ID                               |

#### State Management

| Prop        | Type     | Default         | Description                                                      |
| ----------- | -------- | --------------- | ---------------------------------------------------------------- |
| `open`      | `bool`   | `false`         | Initial open state                                               |
| `openState` | `string` | `'modalIsOpen'` | Name of the Alpine.js state variable for modal open/close state |

#### Content

| Prop            | Type     | Default | Description                                                      |
| --------------- | -------- | ------- | ---------------------------------------------------------------- |
| `title`         | `string` | `null`  | Modal title displayed in header                                  |
| `titleId`       | `string` | `null`  | ARIA labelledby ID (auto-generated if not provided)             |
| `description`   | `string` | `null`  | Modal description (displayed below title)                       |
| `descriptionId` | `string` | `null`  | ARIA describedby ID (auto-generated if not provided)            |

#### Visual Appearance

| Prop          | Type     | Default   | Description                                                                                                 |
| ------------- | -------- | --------- | ----------------------------------------------------------------------------------------------------------- |
| `variant`     | `string` | `'default'` | Modal variant: `'default'`, `'success'`, `'info'`, `'warning'`, `'danger'` (adds border color)            |
| `maxWidth`    | `string` | `'md'`    | Maximum width: `'xs'`, `'sm'`, `'md'`, `'lg'`, `'xl'`, `'2xl'`, `'3xl'`, `'4xl'`, `'5xl'`, `'6xl'`, `'7xl'`, or custom Tailwind class |
| `placement`   | `string \| null` | `null` | Modal placement: `top-left`, `top-center`, `top-right`, `center-left`, `center`, `center-right`, `bottom-left`, `bottom-center`, `bottom-right`. If `null`, uses responsive default (bottom on small screens, center on `sm+`). |
| `class`       | `string` | `''`      | Additional classes for modal container                                                                      |
| `dialogClass` | `string` | `''`      | Additional classes for modal dialog box                                                                     |
| `headerClass` | `string` | `''`      | Additional classes for header section                                                                       |
| `bodyClass`   | `string` | `''`      | Additional classes for body section                                                                         |
| `footerClass` | `string` | `''`      | Additional classes for footer section                                                                        |

#### Backdrop (Theme-Aware)

| Prop              | Type     | Default | Description                                          |
| ----------------- | -------- | ------- | ---------------------------------------------------- |
| `backdropOpacity` | `int`    | `60`    | Backdrop opacity (0-100) for `bg-base-300/{opacity}` |
| `backdropBlur`    | `string` | `'md'`  | Backdrop blur: `none`, `sm`, `md`, `lg`              |
| `backdropClass`   | `string` | `''`    | Override backdrop classes entirely (escape hatch)     |

#### Behavior

| Prop                | Type | Default | Description                                                      |
| ------------------- | ---- | ------- | ---------------------------------------------------------------- |
| `closeOnOutsideClick` | `bool` | `true`  | Close modal when clicking backdrop                              |
| `closeOnEscape`     | `bool` | `true`  | Close modal on ESC key press                                    |
| `trapFocus`         | `bool` | `true`  | Trap focus inside modal (requires Alpine Focus plugin)          |
| `preventScroll`     | `bool` | `true`  | Prevent body scroll when modal is open                          |
| `autoOpen`          | `bool` | `false` | Automatically open modal when rendered                          |

#### Transitions

| Prop                 | Type     | Default     | Description                                                                                                 |
| -------------------- | -------- | ----------- | ----------------------------------------------------------------------------------------------------------- |
| `transition`         | `string` | `'scale-up'` | Transition type: `'fade-in'`, `'scale-up'`, `'scale-down'`, `'slide-up'`, `'slide-down'`, `'unfold'`, `'none'` |
| `transitionDuration` | `int`    | `200`       | Transition duration in milliseconds                                                                        |
| `transitionDelay`    | `int`    | `100`       | Transition delay in milliseconds                                                                           |
| `backdropTransition` | `bool`   | `true`      | Enable backdrop fade transition                                                                             |

#### Close Button

| Prop                | Type     | Default         | Description                                    |
| ------------------- | -------- | --------------- | ---------------------------------------------- |
| `showCloseButton`   | `bool`   | `true`          | Show close button (✕) in header                |
| `closeButtonLabel`  | `string` | `'Close modal'` | ARIA label for close button                    |
| `closeButtonClass`  | `string` | `''`            | Additional classes for close button            |

#### Footer Actions

| Prop         | Type | Default | Description                                    |
| ------------ | ---- | ------- | ---------------------------------------------- |
| `showFooter` | `bool` | `true` | Show footer section                            |

#### Accessibility

| Prop        | Type  | Default      | Description                      |
| ----------- | ----- | ------------ | -------------------------------- |
| `role`      | `string` | `'dialog'` | ARIA role                        |
| `ariaModal` | `bool`   | `true`       | Set `aria-modal="true"` attribute |

#### Advanced

| Prop         | Type     | Default | Description                                                      |
| ------------ | -------- | ------- | ---------------------------------------------------------------- |
| `onOpen`     | `string` | `null`  | Alpine.js expression to execute when modal opens                 |
| `onClose`    | `string` | `null`  | Alpine.js expression to execute when modal closes                |
| `persistent` | `bool`   | `false` | Prevent modal from closing (useful for important modals)        |

### Slots

-   **Default slot:** Main content of the modal (body)
-   **`actions` slot:** Footer actions (buttons, links, etc.)
-   **`footerActions` slot:** Alternative slot name for footer actions

### Usage Examples

#### Basic Base Modal

```blade
<div x-data="{ modalIsOpen: false }">
    <button @click="modalIsOpen = true" class="btn">Open Modal</button>
    
    <x-ui.base-modal
        open-state="modalIsOpen"
        title="Special Offer"
        description="This is a special offer just for you!"
    >
        <p>Upgrade your account now to unlock premium features.</p>
        
        <x-slot:actions>
            <button @click="modalIsOpen = false" class="btn btn-ghost">Remind me later</button>
            <button @click="modalIsOpen = false" class="btn btn-primary">Upgrade Now</button>
        </x-slot:actions>
    </x-ui.base-modal>
</div>
```

#### Modal with Different Variants

```blade
{{-- Success Modal --}}
<x-ui.base-modal
    open-state="successModalIsOpen"
    title="Success!"
    variant="success"
>
    <p>Your action was completed successfully.</p>
</x-ui.base-modal>

{{-- Warning Modal --}}
<x-ui.base-modal
    open-state="warningModalIsOpen"
    title="Warning"
    variant="warning"
>
    <p>Please review your changes before proceeding.</p>
</x-ui.base-modal>

{{-- Danger Modal --}}
<x-ui.base-modal
    open-state="dangerModalIsOpen"
    title="Danger"
    variant="danger"
>
    <p>This action cannot be undone.</p>
</x-ui.base-modal>
```

#### Modal with Different Transitions

```blade
{{-- Fade In --}}
<x-ui.base-modal
    open-state="fadeModalIsOpen"
    title="Fade In"
    transition="fade-in"
>
    <p>This modal fades in smoothly.</p>
</x-ui.base-modal>

{{-- Slide Up --}}
<x-ui.base-modal
    open-state="slideModalIsOpen"
    title="Slide Up"
    transition="slide-up"
>
    <p>This modal slides up from the bottom.</p>
</x-ui.base-modal>

{{-- Unfold --}}
<x-ui.base-modal
    open-state="unfoldModalIsOpen"
    title="Unfold"
    transition="unfold"
>
    <p>This modal unfolds from the top.</p>
</x-ui.base-modal>
```

#### Modal with Custom Placement

```blade
{{-- Top Placement --}}
<x-ui.base-modal
    open-state="topModalIsOpen"
    title="Top Modal"
    placement="top-center"
>
    <p>This modal appears at the top.</p>
</x-ui.base-modal>

{{-- Bottom Placement (Mobile-friendly) --}}
<x-ui.base-modal
    open-state="bottomModalIsOpen"
    title="Bottom Modal"
    placement="bottom-center"
>
    <p>This modal appears at the bottom (great for mobile).</p>
</x-ui.base-modal>
```

#### Modal with Callbacks

```blade
<div x-data="{ 
    modalIsOpen: false,
    onModalOpen() {
        console.log('Modal opened');
        // Perform actions when modal opens
    },
    onModalClose() {
        console.log('Modal closed');
        // Perform cleanup when modal closes
    }
}">
    <button @click="modalIsOpen = true" class="btn">Open Modal</button>
    
    <x-ui.base-modal
        open-state="modalIsOpen"
        title="Modal with Callbacks"
        on-open="onModalOpen()"
        on-close="onModalClose()"
    >
        <p>This modal has open and close callbacks.</p>
    </x-ui.base-modal>
</div>
```

#### Persistent Modal (Cannot be Closed)

```blade
<x-ui.base-modal
    open-state="persistentModalIsOpen"
    title="Important Notice"
    persistent
>
    <p>You must complete this action before proceeding.</p>
    
    <x-slot:actions>
        <button @click="completeAction()" class="btn btn-primary">Complete Action</button>
    </x-slot:actions>
</x-ui.base-modal>
```

#### Modal without Close Button

```blade
<x-ui.base-modal
    open-state="noCloseModalIsOpen"
    title="No Close Button"
    :show-close-button="false"
>
    <p>This modal cannot be closed with the X button.</p>
</x-ui.base-modal>
```

#### Modal with Custom Styling

```blade
<x-ui.base-modal
    open-state="customModalIsOpen"
    title="Custom Styled Modal"
    variant="info"
    max-width="3xl"
    dialog-class="bg-base-200"
    header-class="border-b border-base-300"
    body-class="py-6"
    footer-class="border-t border-base-300"
>
    <p>This modal has custom styling applied to different sections.</p>
</x-ui.base-modal>
```

### Transition Types

The component supports the following transition types:

1. **`fade-in`**: Simple opacity fade
2. **`scale-up`**: Scale from 50% to 100% (default)
3. **`scale-down`**: Scale from 100% to 50%
4. **`slide-up`**: Slide up from bottom
5. **`slide-down`**: Slide down from top
6. **`unfold`**: Unfold from top (scale-y animation)
7. **`none`**: No transition

### Keyboard Navigation

The modal supports full keyboard navigation:

-   **Tab**: Move focus to next focusable element
-   **Shift + Tab**: Move focus to previous focusable element
-   **Enter/Space**: Activate focused element
-   **ESC**: Close modal (if `closeOnEscape` is `true`)

### Focus Trapping

When `trapFocus` is `true` (default), the modal uses Alpine.js Focus plugin to trap focus inside the modal. This ensures:

-   Focus stays within the modal
-   Tab navigation cycles through modal elements only
-   Background content is not accessible via keyboard

**Note:** Focus trapping requires the Alpine.js Focus plugin. If you don't have it installed, set `trapFocus` to `false`.

### Accessibility Features

-   **ARIA attributes**: Automatically sets `role="dialog"`, `aria-modal="true"`, `aria-labelledby`, and `aria-describedby`
-   **Focus management**: Traps focus inside modal when open
-   **Keyboard support**: Full keyboard navigation and ESC key support
-   **Screen reader support**: Proper ARIA labels and descriptions

### Implementation Details

-   Uses pure Alpine.js for state management (no `<dialog>` element)
-   Follows Penguin UI modal patterns and best practices
-   Supports all transition types from Penguin UI documentation
-   Fully accessible with ARIA attributes and keyboard navigation
-   Compatible with Livewire components
-   Uses `x-cloak` to prevent flash of unstyled content

### Usage Pattern

All modals in the project use `<x-ui.base-modal>` directly with Alpine.js state management. The component uses Alpine.js `x-data` and `x-show` directives for state management, making it fully reactive and compatible with Livewire components.

#### Basic Modal with Alpine.js State

```blade
<div x-data="{ modalIsOpen: false }">
    <button @click="modalIsOpen = true" class="btn">Open Modal</button>
    
    <x-ui.base-modal id="my-modal" open-state="modalIsOpen" title="Hello!">
        <p>Press ESC key or click outside to close</p>
    </x-ui.base-modal>
</div>
```

#### Modal with External State Control

```blade
<div x-data="{ deleteAccountModalOpen: false }">
    <button @click="deleteAccountModalOpen = true" class="btn">Delete Account</button>
    
    <x-ui.base-modal id="delete-modal" open-state="deleteAccountModalOpen" title="Confirm Deletion">
        <p>Are you sure you want to delete your account?</p>
        
        <x-slot:actions>
            <button @click="deleteAccountModalOpen = false" class="btn btn-ghost">Cancel</button>
            <button @click="deleteAccount()" class="btn btn-error">Delete</button>
        </x-slot:actions>
    </x-ui.base-modal>
</div>
```

#### Auto-Open Modal

```blade
@if ($showModal)
    <x-ui.base-modal id="auto-modal" :auto-open="true" title="Auto Opened">
        <p>This modal opens automatically when rendered</p>
    </x-ui.base-modal>
@endif
```

### Current Usage in Project

1. **Two-Factor Setup Modal** (`resources/views/components/settings/two-factor/⚡setup-modal.blade.php`)
    - Uses `autoOpen="true"` to automatically open when component renders
    - Includes form for OTP verification
    - Uses `actions` slot for Continue button

2. **Delete User Confirmation Modal** (`resources/views/components/settings/⚡delete-user-form.blade.php`)
    - Uses Alpine.js state (`deleteAccountModalOpen`) to control visibility
    - Contains password confirmation form
    - Uses `actions` slot for Cancel and Delete buttons

3. **Confirm Modal** (`resources/views/components/ui/confirm-modal.blade.php`)
    - Wraps `<x-ui.base-modal>` for confirmation dialogs
    - Supports external state control via `open-state` prop
    - Can be triggered via Alpine.js events or direct state management

---

