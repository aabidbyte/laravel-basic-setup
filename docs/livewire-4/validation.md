## Validation

Livewire provides comprehensive validation features.

### #[Validate] Attribute

Validate properties:

```php
use Livewire\Attributes\Validate;

#[Validate('required|max:255')]
public string $title = '';

#[Validate('required|min:10')]
public string $content = '';
```

### rules() Method

Define rules in method:

```php
public function rules(): array
{
    return [
        'title' => 'required|max:255',
        'content' => 'required|min:10',
    ];
}
```

### Real-Time Validation

Validate in real-time:

```blade
<input type="text" wire:model.live.blur="title">
@error('title') <span>{{ $message }}</span> @enderror
```

### Custom Messages/Attributes

Custom messages:

```php
public function messages(): array
{
    return [
        'title.required' => 'The title field is required.',
        'content.min' => 'The content must be at least 10 characters.',
    ];
}
```

Custom attributes:

```php
public function attributes(): array
{
    return [
        'title' => 'post title',
        'content' => 'post content',
    ];
}
```

### Form Objects

Use form objects for validation:

```php
class PostForm extends Form
{
    #[Validate('required|max:255')]
    public string $title = '';

    public function save(): void
    {
        $this->validate();
        // Save logic
    }
}
```

### Rule Objects

Use rule objects:

```php
use Illuminate\Validation\Rules\Password;

public function rules(): array
{
    return [
        'password' => ['required', Password::min(8)],
    ];
}
```

### Manual Error Control

Manually set errors:

```php
$this->addError('title', 'Custom error message');
```

Clear errors:

```php
$this->resetErrorBag();
$this->resetValidation();
```

### Validator Instance

Get validator instance:

```php
$validator = $this->getValidatorInstance();
```

### Custom Validators

Create custom validators:

```php
Validator::extend('custom_rule', function ($attribute, $value, $parameters) {
    return $value === 'expected';
});
```

### Testing

Test validation:

```php
Livewire::test(CreatePost::class)
    ->set('title', '')
    ->call('save')
    ->assertHasErrors(['title' => 'required']);
```

### JavaScript Access

Access errors in JavaScript:

```javascript
if ($wire.$errors.has("title")) {
    console.log($wire.$errors.first("title"));
}
```

### Deprecated #[Rule]

The `#[Rule]` attribute is deprecated. Use `#[Validate]` instead.

