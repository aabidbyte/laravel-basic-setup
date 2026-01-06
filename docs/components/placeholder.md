# Placeholder Component

Typed skeleton placeholders for Livewire lazy loading using DaisyUI skeleton classes.

## Usage

```blade
{{-- Default: centered spinner --}}
<x-ui.placeholder></x-ui.placeholder>

{{-- Table skeleton --}}
<x-ui.placeholder type="table" rows="5" columns="4"></x-ui.placeholder>

{{-- Form skeleton --}}
<x-ui.placeholder type="form" rows="4"></x-ui.placeholder>

{{-- Card skeleton (user profile style) --}}
<x-ui.placeholder type="card" rows="3"></x-ui.placeholder>

{{-- List skeleton --}}
<x-ui.placeholder type="list" rows="5"></x-ui.placeholder>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `type` | `string` | `default` | Skeleton type: `default`, `table`, `form`, `card`, `list` |
| `rows` | `int` | `3` | Number of skeleton rows |
| `columns` | `int` | `4` | Number of table columns (table type only) |
| `class` | `string` | `''` | Additional container classes |

## Skeleton Types

| Type | Pattern |
|------|---------|
| `default` | Centered loading spinner |
| `table` | Header row + data rows with checkbox column |
| `form` | Labels + input field skeletons + submit button |
| `card` | Avatar + title + content sections |
| `list` | Stacked card items with icons |

## Using with Livewire SFCs

Add the `@placeholder` directive to your SFC pages, rendering a placeholder via a view:

```blade
<?php
new class extends BasePageComponent {
    // ...
}; ?>

@placeholder
    <x-ui.placeholder type="form" rows="4"></x-ui.placeholder>
@endplaceholder

<section>
    {{-- Actual content --}}
</section>
```

> **Note**: The `@placeholder` directive with `<x-ui.placeholder>` is the "rendering a placeholder via a view" approach for SFCs. For class-based components, you can define a `placeholder()` method that returns a view.

## Class-Based Component Usage

For class-based Livewire components, use the `placeholder()` method:

```php
use Livewire\Attributes\Lazy;

#[Lazy]
class UserProfile extends Component
{
    public function placeholder()
    {
        return view('components.ui.placeholder', ['type' => 'card']);
    }
}
```

## Configuration

Default placeholder is set in `config/livewire.php`:

```php
'component_placeholder' => 'components.ui.placeholder',
```
