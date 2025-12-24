## Forms

Forms in Livewire provide validation, error handling, and user feedback.

### Submission

Submit forms using `wire:submit`:

```blade
<form wire:submit="save">
    <input type="text" wire:model="title">
    <button type="submit">Save</button>
</form>
```

### Validation

Validate form data in actions:

```php
public function save()
{
    $this->validate([
        'title' => 'required|max:255',
        'content' => 'required|min:10',
    ]);

    // Save logic
}
```

Or use the `#[Validate]` attribute:

```php
use Livewire\Attributes\Validate;

#[Validate('required|max:255')]
public string $title = '';

#[Validate('required|min:10')]
public string $content = '';
```

### Form Objects

Use form objects for complex forms:

```php
use Livewire\Form;

class PostForm extends Form
{
    #[Validate('required|max:255')]
    public string $title = '';

    #[Validate('required|min:10')]
    public string $content = '';

    public function save(): void
    {
        $this->validate();

        Post::create($this->only(['title', 'content']));
    }
}
```

Use in component:

```php
public PostForm $form;

public function save()
{
    $this->form->save();
}
```

### Resetting/Pulling Fields

Reset form fields:

```php
$this->reset('title', 'content');
$this->form->reset();
```

Pull field values:

```php
$title = $this->pull('title');
```

### Rule Objects

Use rule objects for reusable validation:

```php
use Illuminate\Validation\Rules\Password;

public function save()
{
    $this->validate([
        'password' => ['required', Password::min(8)->letters()->numbers()],
    ]);
}
```

### Loading Indicators

Show loading states:

```blade
<button wire:click="save" wire:loading.attr="disabled">
    <span wire:loading.remove>Save</span>
    <span wire:loading>Saving...</span>
</button>
```

Or use `data-loading` attribute:

```blade
<button wire:click="save" class="data-loading:opacity-50">
    Save
</button>
```

### Live Updating

Update in real-time:

```blade
<input type="text" wire:model.live="title">
```

### Blur/Change Updates

Update on blur or change:

```blade
<input type="text" wire:model.blur="title">
<input type="text" wire:model.change="title">
```

### Real-Time Validation/Saving

Validate or save as user types:

```blade
<input type="text" wire:model.live.debounce.500ms="title" wire:model.live.blur="validateTitle">
```

### Dirty Indicators

Show when form is dirty:

```blade
<div wire:dirty>You have unsaved changes</div>
<div wire:dirty.class="text-red-500">Unsaved</div>
```

### Debouncing/Throttling Input

Debounce or throttle input:

```blade
<input wire:model.live.debounce.300ms="search">
<input wire:model.live.throttle.500ms="search">
```

### Blade Components for Inputs

This project uses **DaisyUI** for styling. Use DaisyUI components and theme-aware classes:

```blade
<x-ui.input
    type="text"
    wire:model="title"
    label="Title"
    name="title"
/>

<x-ui.input
    type="textarea"
    wire:model="content"
    label="Content"
    name="content"
/>

<div class="form-control w-full">
    <label class="label">
        <span class="label-text">Category</span>
    </label>
    <select wire:model="category" class="select select-bordered w-full">
        <option value="">Select...</option>
    </select>
</div>
```

### Custom Form Controls

Create custom form controls:

```blade
<div wire:ignore>
    <input type="text" id="custom-input">
</div>

<script>
document.getElementById('custom-input').addEventListener('input', (e) => {
    $wire.set('title', e.target.value);
});
</script>
```

