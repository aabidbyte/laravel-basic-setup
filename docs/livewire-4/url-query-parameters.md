## URL Query Parameters

Sync component properties with URL query parameters.

### #[Url] Attribute

Sync property with URL:

```php
use Livewire\Attributes\Url;

#[Url]
public string $search = '';
```

### Basic Usage

```php
#[Url]
public string $search = '';

#[Url]
public int $page = 1;
```

### Initializing from URL

Properties are automatically initialized from URL:

```php
// URL: /posts?search=test&page=2
// $search = 'test', $page = 2
```

### Nullable

Allow null values:

```php
#[Url]
public ?string $search = null;
```

### Alias

Use alias in URL:

```php
#[Url(as: 'q')]
public string $search = '';
// URL: /posts?q=test
```

### Excluding Values

Exclude default values:

```php
#[Url(except: '')]
public string $search = '';
// Only appears in URL if not empty
```

### Display on Load

Show in URL on component load:

```php
#[Url(keep: true)]
public string $search = '';
```

### History

Control browser history:

```php
#[Url(history: false)]
public string $search = '';
```

### queryString() Method

Define query string in method:

```php
protected function queryString(): array
{
    return [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];
}
```

### Trait Hooks

Use trait hooks:

```php
trait WithSearch
{
    #[Url]
    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
```

