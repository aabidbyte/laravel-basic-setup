# wire:submit

`wire:submit` is used to handle form submissions in Livewire components.

## Basic Usage

```blade
<form wire:submit="save">
    <input wire:model="title">
    <button>Submit</button>
</form>
```

## Modifiers

-   `.prevent`: Automatically prevents the default form submission (form reload). This is the default behavior in Livewire when using `wire:submit`, but can be explicitly stated.
-   `.throttle.1000ms`: Prevents double submissions by throttling the form submission.

### Success Message Patterns

You can combine `wire:submit` with `wire:loading` to provide feedback during submission:

```blade
<form wire:submit="save">
    ...
    <button type="submit" class="data-loading:opacity-50">
        <span wire:loading.remove>Save</span>
        <span wire:loading>Saving...</span>
    </button>
</form>
```

## Form Objects

Standard practice in Livewire 4 is to use Form Objects for cleaner code:

```php
public PostForm $form;

public function save()
{
    $this->form->store();
}
```
