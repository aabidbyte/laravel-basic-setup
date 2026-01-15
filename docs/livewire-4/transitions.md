# Transitions

Livewire 4 introduces smooth, hardware-accelerated transitions using the browser's View Transitions API.

## wire:transition

The `wire:transition` directive automatically applies smooth fade transitions when elements are shown or hidden.

```blade
@if ($showAlertMessage)
    <div wire:transition>
        <!-- Message smoothly fades in/out -->
        Operation successful!
    </div>
@endif
```

## Component Transitions

For navigation or step-based interfaces, you can define transition types on your component methods using the `#[Transition]` attribute.

```php
use Livewire\Attributes\Transition;

class Wizard extends Component
{
    public $step = 1;

    #[Transition(type: 'forward')]
    public function next()
    {
        $this->step++;
    }

    #[Transition(type: 'backward')]
    public function previous()
    {
        $this->step--;
    }
}
```

## Customizing Transitions

You can customize the animations using standard CSS pseudo-elements provided by the View Transitions API:

```css
::view-transition-old(root) {
    /* Outgoing state */
}

::view-transition-new(root) {
    /* Incoming state */
}
```
