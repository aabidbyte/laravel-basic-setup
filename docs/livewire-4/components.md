## Components

Livewire components are the building blocks of your application. They can be created in three formats: single-file components (SFC), multi-file components (MFC), or class-based components.

### Single-File Components (SFC)

Single-file components combine PHP and Blade in one file. By default, view-based component files are prefixed with a ⚡ emoji to distinguish them from regular Blade files in your editor and searches.

```bash
php artisan make:livewire create-post        # Single-file (default)
```

Example single-file component:

```php
<?php

use Livewire\Component;

new class extends Component
{
    public string $title = '';

    public string $content = '';

    public function save()
    {
        $this->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        // Save logic here
    }
};
?>

<form wire:submit="save">
    <label>
        Title
        <input type="text" wire:model="title">
        @error('title') <span style="color: red;">{{ $message }}</span> @enderror
    </label>

    <label>
        Content
        <textarea wire:model="content" rows="5"></textarea>
        @error('content') <span style="color: red;">{{ $message }}</span> @enderror
    </label>

    <button type="submit">Save Post</button>
</form>
```

### Multi-File Components (MFC)

Multi-file components organize PHP, Blade, JavaScript, and tests in a directory.

```bash
php artisan make:livewire create-post --mfc  # Multi-file
```

This creates:

```
app/Livewire/CreatePost.php
resources/views/livewire/create-post.blade.php
resources/js/livewire/create-post.js (optional)
tests/Feature/Livewire/CreatePostTest.php (if --test flag used)
```

### Class-Based Components

Traditional class-based components separate PHP logic from the view:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class CreatePost extends Component
{
    public string $title = '';

    public string $content = '';

    public function save()
    {
        $this->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        // Save logic here
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

### Page Components

Page components are full-page Livewire components that act as complete pages:

```php
<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return view('livewire.dashboard');
    }
};
?>
```

Route them using `Route::livewire()`:

```php
Route::livewire('/dashboard', 'pages::dashboard');
```

### Rendering Components

Components can be rendered in several ways:

**In Blade templates:**

```blade
<livewire:create-post />
<livewire:create-post :title="$post->title" />
```

**Using the component helper:**

```blade
@livewire('create-post', ['title' => 'My Post'])
```

**In PHP:**

```php
Livewire::mount('create-post', ['title' => 'My Post'])->html();
```

### Passing Props

Props are passed to components as attributes:

```blade
<livewire:user-profile :user="$user" :show-email="true" />
```

In the component:

```php
public User $user;
public bool $showEmail = false;
```

### Organizing Components

Organize components in directories that match your application structure:

```
resources/views/
├── components/
│   └── ui/              # Reusable UI components
├── livewire/
│   ├── posts/           # Post-related components
│   └── users/           # User-related components
└── pages/               # Full-page components
    ├── dashboard.php
    └── settings.php
```

Use namespaces in `config/livewire.php`:

```php
'component_namespaces' => [
    'pages' => resource_path('views/pages'),
    'components' => resource_path('views/components'),
    'admin' => resource_path('views/admin'), // Custom namespace
],
```

### Namespaces

Livewire 4 encourages a structured approach with namespaces. By default:

- `pages::` points to page components.
- `layouts::` points to layouts.
- Everything else is in `resources/views/components`.

You can register custom namespaces in `config/livewire.php` to organize your application modules (e.g., `admin::`, `billing::`).

## Inline Placeholders

For lazy components and islands, you can define loading states directly within the component using the `@placeholder` directive. This eliminates the need for separate placeholder views.

```blade
@placeholder
    <div class="animate-pulse h-32 bg-gray-200 rounded"></div>
@endplaceholder

<div>
    <!-- Actual content loads here -->
    <h1>{{ $title }}</h1>
</div>
```

### Converting Between Formats

```bash
php artisan livewire:convert create-post
```

This command converts between SFC, MFC, and class-based formats.

