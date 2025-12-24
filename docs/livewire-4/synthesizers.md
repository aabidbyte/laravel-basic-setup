## Synthesizers

Synthesizers handle custom property types in Livewire.

### Understanding

Synthesizers convert custom types to/from arrays for storage.

### $key

Each synthesizer has a unique key:

```php
public static $key = 'address';
```

### match()

Determine if synthesizer handles a value:

```php
public static function match($target, $value): bool
{
    return $value instanceof Address;
}
```

### dehydrate()

Convert to array:

```php
public static function dehydrate($target, $value, $context): array
{
    return [
        'street' => $value->street,
        'city' => $value->city,
    ];
}
```

### hydrate()

Convert from array:

```php
public static function hydrate($value, $target, $context): Address
{
    return new Address($value['street'], $value['city']);
}
```

### Registering

Register in service provider:

```php
Livewire::propertySynthesizer(AddressSynthesizer::class);
```

### Data Binding

Synthesizers work automatically with `wire:model`:

```blade
<input wire:model="address.street">
```

