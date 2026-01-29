# Accordion Component

**Location:** `resources/views/components/ui/accordion.blade.php`

**Component Name:** `<x-ui.accordion>`

## Description

A flexible accordion component built on top of [DaisyUI Collapse](https://daisyui.com/components/collapse/). It supports individual toggle behavior (checkbox) or grouped accordion behavior (radio), along with styling options for arrows, icons, and descriptions.

## Props

| Prop | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `name` | `string` | `null` | Group name for radio behavior. If set, acts as a proper accordion (only one open at a time). If `null`, acts as independent collapsible (checkbox). |
| `open` | `bool` | `false` | Initial open state. |
| `title` | `string` | `''` | The main header text of the accordion item. |
| `description` | `string` | `''` | Optional helper text displayed below the title. |
| `icon` | `string` | `null` | Optional definition for an icon to display next to the header (uses `x-ui.icon`). |
| `arrow` | `bool` | `true` | Show the default chevron arrow (DaisyUI `collapse-arrow`). |
| `plus` | `bool` | `false` | Show a plus/minus indicator (DaisyUI `collapse-plus`). Mutually exclusive with `arrow`. |
| `forceOpen` | `bool` | `false` | Forces the `collapse-open` class. Useful for static overrides or desktop views. |
| `enabled` | `bool` | `true` | If `false`, renders only the content (slot) without the accordion wrapper structure. |

## Usage Examples

### Independent Collapsible (Checkbox)

```blade
<x-ui.accordion title="Click to Open">
    <p>This content is hidden by default.</p>
</x-ui.accordion>
```

### Grouped Accordion (Radio)

Use the `name` prop to group items. Only one can be open at a time.

```blade
<x-ui.accordion name="faq" title="Question 1">
    <p>Answer 1...</p>
</x-ui.accordion>

<x-ui.accordion name="faq" title="Question 2" open>
    <p>Answer 2 (Open by default)...</p>
</x-ui.accordion>

<x-ui.accordion name="faq" title="Question 3">
    <p>Answer 3...</p>
</x-ui.accordion>
```

### With Description and Icon

```blade
<x-ui.accordion 
    title="Advanced Settings" 
    description="Manage your advanced configuration"
    icon="cog"
    plus
>
    <!-- Content -->
</x-ui.accordion>
```

### Custom Styling

You can add standard Tailwind classes to the wrapper:

```blade
<x-ui.accordion title="Styled" class="bg-primary text-primary-content">
    <p>Custom colored accordion.</p>
</x-ui.accordion>
```
