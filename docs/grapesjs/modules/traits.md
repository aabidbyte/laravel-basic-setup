# Trait Manager

Traits are used to update HTML attributes of a component (e.g., placeholder, title, target).

## Built-in Trait Types

- `text`: Simple text input.
- `number`: Number input.
- `checkbox`: Checkbox input.
- `select`: Dropdown selection.
- `color`: Color picker.
- `button`: Triggers a function.

## Add Traits to Components

```javascript
editor.DomComponents.addType('input-type', {
  model: {
    defaults: {
      traits: [
        {
          type: 'text',
          label: 'Placeholder',
          name: 'placeholder',
        },
        {
          type: 'select',
          label: 'Type',
          name: 'type',
          options: [
            { value: 'text', name: 'Text' },
            { value: 'email', name: 'Email' },
            { value: 'password', name: 'Password' },
          ],
        },
      ],
    },
  },
});
```

## Define New Trait Type

You can define custom trait types:

```javascript
editor.TraitManager.addType('my-custom-trait', {
  // Logic for rendering and handling the trait
});
```
