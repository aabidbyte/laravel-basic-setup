# Merge Tag Picker Component

The `x-ui.merge-tag-picker` component provides a user-friendly interface for selecting and inserting merge tags (e.g., `{{ user.first_name }}`) into form fields or text areas.

It features a two-step navigation flow (select entity type -> select tag), search functionality, and entity-specific color coding.

## Usage

### Basic Usage

Use the component as a self-closing tag. The `target` prop is required.

```blade
<x-ui.merge-tag-picker target="content" />
```

### With Entity Types

By default, the component discovers tags for all registered entities. You can limit this by passing specific entity types.

```blade
<x-ui.merge-tag-picker target="content" :entity-types="['user', 'team']" />
```

### With Context Variables

You can pass additional context-specific variables that are not part of a standard entity model.

```blade
<x-ui.merge-tag-picker target="content" :context-variables="['order_id', 'total_amount']" />
```

## Props

| Prop | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `target` | `string` | `null` | **Required.** The `name` attribute, `id`, or Alpine `x-ref` of the input field to insert the tag into. |
| `entityTypes` | `array` | `[]` | List of entity types to show tags for (e.g., `['user']`). If empty, shows all available types. |
| `contextVariables` | `array` | `[]` | List of additional variables to show under a "Context" group. |

## Features

- **Two-Step Navigation**: Users first select an entity type (User, Team, etc.) and then select a specific tag.
- **Search**: A search input filters tags within the selected entity group.
- **Entity Colors**: Each entity type has a distinct color (Primary, Secondary, Accent, etc.) defined in `EntityTypeRegistry`.
- **Smart Focus**: After inserting a tag, the cursor focus is automatically restored to the input field at the correct position.
- **CSP Compatible**: Fully compatible with strict Content Security Policies (no `unsafe-eval` or inline `JSON.parse`).
