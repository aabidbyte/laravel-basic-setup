# Livewire 4 Documentation

> **Important**: Livewire v4 is currently in beta. It's recommended to test thoroughly in a development environment before upgrading production applications. Breaking changes may occur between beta releases.

> **Note**: This project uses **DaisyUI** as its component library for Tailwind CSS. Always use DaisyUI's theme-aware classes (e.g., `bg-base-100`, `text-base-content`, `btn-primary`) instead of hardcoded color classes. Reusable Blade components are available in `resources/views/components/ui/`.

## AI-Friendly Index

This index is designed for AI assistants to quickly locate specific topics and find detailed information. Each section includes cross-references and related topics.

### Quick Reference by Topic

**Core Concepts:**

-   [Components](#components) - Single-file, multi-file, class-based, page components, rendering, props, organizing
-   [Properties](#properties) - Initializing, bulk assignment, data binding, resetting, pulling, types, wireables, synthesizers, JavaScript access, security, computed, session, URL query
-   [Actions](#actions) - Basic usage, parameters, dependency injection, event listeners, magic actions, JavaScript actions, skipping re-renders, async, preserving scroll, security
-   [Forms](#forms) - Submission, validation, form objects, resetting/pulling fields, rule objects, loading indicators, live updating, blur/change updates, real-time validation/saving, dirty indicators, debouncing/throttling, Blade components, custom controls
-   [Events](#events) - Dispatching, listening, dynamic event names, child component events, JavaScript interaction, Alpine events, direct dispatching, testing, Laravel Echo integration
-   [Lifecycle Hooks](#lifecycle-hooks) - mount, boot, update, hydrate, dehydrate, render, exception, trait hooks, form object hooks

**Advanced Features:**

-   [Nesting Components](#nesting-components) - Independent nature, passing props, loops, reactive props, wire:model binding, slots, HTML attributes, Islands vs nested, event communication, direct parent access, dynamic/recursive components, forcing re-render
-   [AlpineJS Integration](#alpinejs-integration) - x-data, x-text, x-on, $wire object (properties, methods, refresh, dispatch, on, el, get, set, toggle, call, js, entangle, watch, upload, intercept), manual bundling
-   [Navigation](#navigation) - wire:navigate, redirects, prefetching, @persist, active links, scroll position, JavaScript hooks, manual navigation, analytics, script evaluation, progress bar customization
-   [Islands](#islands) - @island directive, lazy loading, deferred loading, custom placeholders, named islands, append/prepend modes, nested islands, always render, skip initial render, polling, data/loop/conditional scope
-   [Lazy Loading](#lazy-loading) - lazy vs defer, basic usage, placeholder HTML, immediate loading, props, enforcing defaults, bundling, full-page loading, default placeholder, disabling for tests
-   [Loading States](#loading-states) - data-loading attribute, wire:loading directive, basic usage, styling with Tailwind/CSS, advantages, delays, targets

**Validation & Data:**

-   [Validation](#validation) - #[Validate] attribute, rules() method, real-time, custom messages/attributes, form objects, rule objects, manual error control, validator instance, custom validators, testing, JavaScript access, deprecated #[Rule]
-   [File Uploads](#file-uploads) - WithFileUploads trait, wire:model on file inputs, storing, multiple files, validation, temporary preview URLs, testing, S3 direct upload, loading/progress indicators, cancelling, JavaScript API, configuration
-   [Pagination](#pagination) - WithPagination trait, basic usage, URL query string tracking, scroll behavior, resetting page, multiple paginators, hooks, simple/cursor pagination, Bootstrap/Tailwind themes, custom views
-   [URL Query Parameters](#url-query-parameters) - #[Url] attribute, basic usage, initializing from URL, nullable, alias, excluding values, display on load, history, queryString() method, trait hooks
-   [File Downloads](#file-downloads) - Standard Laravel responses, streaming, testing

**UI & Interaction:**

-   [Teleport](#teleport) - @teleport directive, basic usage, why use, common use cases, constraints, Alpine integration
-   [Morphing](#morphing) - How it works, shortcomings, internal look-ahead, morph markers, wrapping conditionals, wire:replace

**Advanced Technical:**

-   [Hydration](#hydration) - Dehydrating HTML/JSON snapshot, hydrating, advanced hydration with tuples/metadata, custom property types with Synthesizers
-   [Synthesizers](#synthesizers) - Understanding, $key, match(), dehydrate(), hydrate(), registering, data binding
-   [JavaScript](#javascript) - Script execution, $wire object, loading assets @assets, interceptors (component, message, request), global Livewire events, Livewire global object, Livewire.hook(), custom directives, server-side JS evaluation, common patterns, best practices, debugging, $wire reference, snapshot object, component object, message payload

**Testing & Troubleshooting:**

-   [Testing](#testing) - Pest, browser testing, views, authentication, properties, actions, validation, authorization, redirects, events, PHPUnit
-   [Troubleshooting](#troubleshooting) - Component mismatches, wire:key, duplicate keys, multiple Alpine instances, missing @alpinejs/ui

**Security & Configuration:**

-   [Security](#security) - Authorizing action parameters/public properties, model properties, #[Locked] attribute, middleware persistence, snapshot checksums
-   [CSP](#csp) - CSP-safe build, what's supported/not, headers, performance, testing

### Search Keywords

When searching for specific functionality, use these keywords:

-   **Component creation**: `make:livewire`, `single-file`, `multi-file`, `class-based`, `SFC`, `MFC`
-   **Data binding**: `wire:model`, `wire:model.live`, `wire:model.defer`, `properties`, `computed`
-   **User interaction**: `wire:click`, `wire:submit`, `wire:change`, `wire:blur`, `actions`
-   **Forms**: `validation`, `#[Validate]`, `form objects`, `rule objects`, `errors`
-   **Events**: `dispatch`, `listen`, `#[On]`, `$dispatch`, `$listen`
-   **Loading**: `wire:loading`, `data-loading`, `lazy`, `defer`, `islands`
-   **Navigation**: `wire:navigate`, `@persist`, `prefetch`, `scroll`
-   **Files**: `file uploads`, `WithFileUploads`, `temporary preview`, `S3`
-   **Pagination**: `WithPagination`, `paginate()`, `links()`
-   **URL**: `#[Url]`, `queryString()`, `query parameters`
-   **Testing**: `Livewire::test()`, `Volt::test()`, `browser testing`, `Pest`
-   **Security**: `#[Locked]`, `authorize`, `middleware`, `checksums`

---

## Overview

Livewire allows you to build dynamic, reactive interfaces using only PHP—no JavaScript required. Instead of writing frontend code in JavaScript frameworks, you write simple PHP classes and Blade templates, and Livewire handles all the complex JavaScript behind the scenes.

## Installation

From the root directory of your Laravel app, run the following Composer command:

```bash
composer require livewire/livewire:^4.0@beta
```

After updating, clear your application's cache:

```bash
php artisan optimize:clear
```

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
],
```

### Converting Between Formats

```bash
php artisan livewire:convert create-post
```

This command converts between SFC, MFC, and class-based formats.

## Properties

Properties store component state and can be accessed in both PHP and JavaScript.

### Initializing Properties

Properties can be initialized with default values:

```php
public string $title = '';
public int $count = 0;
public array $items = [];
public ?User $user = null;
```

### Bulk Assignment

Use `fill()` for bulk assignment:

```php
$this->fill([
    'title' => 'New Title',
    'content' => 'New Content',
]);
```

Or use `fillOnly()` / `fillExcept()`:

```php
$this->fillOnly(['title', 'content'], $request->all());
$this->fillExcept(['password'], $request->all());
```

### Data Binding

Bind properties to form inputs using `wire:model`:

```blade
<input type="text" wire:model="title">
<textarea wire:model="content"></textarea>
<select wire:model="category">
    <option value="">Select...</option>
</select>
```

**Modifiers:**

-   `wire:model.live` - Updates in real-time (default in v4)
-   `wire:model.defer` - Updates on blur/change
-   `wire:model.lazy` - Updates on blur
-   `wire:model.debounce.300ms` - Debounces updates

### Resetting Properties

Reset properties to their initial values:

```php
$this->reset('title', 'content');
$this->reset(['title', 'content']);
$this->reset(); // Reset all properties
```

### Pulling Properties

Pull properties from the component's state:

```php
$title = $this->pull('title');
```

### Supported Types

Livewire supports many PHP types:

-   **Primitives**: `string`, `int`, `float`, `bool`, `array`
-   **Objects**: Eloquent models, DTOs, collections
-   **Wireables**: Classes implementing `Wireable` interface
-   **Synthesizers**: Custom property types

### Wireables

Wireables allow custom objects to be stored as properties:

```php
use Livewire\Wireable;

class Address implements Wireable
{
    public function __construct(
        public string $street,
        public string $city,
    ) {}

    public function toLivewire(): array
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
        ];
    }

    public static function fromLivewire($value): static
    {
        return new static(
            $value['street'],
            $value['city']
        );
    }
}
```

Use in component:

```php
public Address $address;
```

### Synthesizers

Synthesizers handle custom property types automatically:

```php
use Livewire\Mechanisms\HandleProperties\Synthesizers\Synthesizer;

class AddressSynthesizer extends Synthesizer
{
    public static $key = 'address';

    public static function match($target, $value): bool
    {
        return $value instanceof Address;
    }

    public static function dehydrate($target, $value, $context): array
    {
        return [
            'street' => $value->street,
            'city' => $value->city,
        ];
    }

    public static function hydrate($value, $target, $context): Address
    {
        return new Address($value['street'], $value['city']);
    }
}
```

Register in service provider:

```php
Livewire::propertySynthesizer(AddressSynthesizer::class);
```

### Accessing from JavaScript

Access properties from JavaScript using `$wire`:

```javascript
$wire.title = "New Title";
let title = $wire.title;
```

### Security Concerns

**Public properties are exposed to the frontend.** Never store sensitive data in public properties:

```php
// ❌ Bad
public string $password = '';

// ✅ Good
#[Locked]
public string $apiKey = '';
```

Use the `#[Locked]` attribute to prevent frontend modification:

```php
use Livewire\Attributes\Locked;

#[Locked]
public string $secret = 'my-secret';
```

### Computed Properties

Computed properties are calculated on-demand:

```php
use Livewire\Attributes\Computed;

#[Computed]
public function fullName(): string
{
    return "{$this->firstName} {$this->lastName}";
}
```

Access in Blade:

```blade
{{ $this->fullName }}
```

### Session Properties

Store properties in the session:

```php
use Livewire\Attributes\Session;

#[Session]
public string $search = '';
```

### URL Query Parameters

Sync properties with URL query parameters:

```php
use Livewire\Attributes\Url;

#[Url]
public string $search = '';

#[Url(as: 'q')]
public string $query = '';
```

## Actions

Actions are methods that handle user interactions and can be called from the frontend.

### Basic Usage

Define actions as public methods:

```php
public function save()
{
    // Save logic
}
```

Call from Blade:

```blade
<button wire:click="save">Save</button>
```

### Parameters

Pass parameters to actions:

```blade
<button wire:click="delete({{ $post->id }})">Delete</button>
```

```php
public function delete(int $id)
{
    Post::find($id)->delete();
}
```

### Dependency Injection

Actions support dependency injection:

```php
public function save(PostService $service)
{
    $service->create($this->title, $this->content);
}
```

### Event Listeners

Listen for events using the `#[On]` attribute:

```php
use Livewire\Attributes\On;

#[On('post-created')]
public function updatePostList($title)
{
    session()->flash('status', "New post created: {$title}");
}
```

### Magic Actions

Magic actions are automatically available:

```blade
<button wire:click="$refresh">Refresh</button>
<button wire:click="$set('title', 'New Title')">Set Title</button>
<button wire:click="$toggle('show')">Toggle</button>
<button wire:click="$reset">Reset All</button>
```

### JavaScript Actions

Call actions from JavaScript:

```javascript
$wire.save();
$wire.delete(123);
```

### Skipping Re-renders

Skip component re-rendering after an action:

```blade
<button wire:click.renderless="trackClick">Track</button>
```

Or in PHP:

```php
use Livewire\Attributes\Renderless;

#[Renderless]
public function trackClick()
{
    // Analytics tracking
}
```

### Async Actions

Run actions asynchronously without blocking:

```blade
<button wire:click.async="logActivity">Track</button>
```

Or in PHP:

```php
use Livewire\Attributes\Async;

#[Async]
public function logActivity()
{
    // Non-blocking operation
}
```

### Preserving Scroll

Preserve scroll position during updates:

```blade
<button wire:click.preserve-scroll="loadMore">Load More</button>
```

### Security

Always validate and authorize action parameters:

```php
public function delete(int $id)
{
    $post = Post::findOrFail($id);

    $this->authorize('delete', $post);

    $post->delete();
}
```

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

## Events

Events allow components to communicate with each other.

### Dispatching Events

Dispatch events from components:

```php
use Livewire\Attributes\On;

public function save()
{
    // Save logic

    $this->dispatch('post-created', title: $this->title);
}
```

Or use the `$dispatch()` method:

```php
$this->dispatch('post-created', title: $this->title);
```

### Listening for Events

Listen for events using `#[On]`:

```php
#[On('post-created')]
public function updatePostList($title)
{
    $this->posts = Post::latest()->get();
}
```

### Dynamic Event Names

Use dynamic event names:

```php
$this->dispatch("post-{$action}-completed", id: $this->postId);
```

### Child Component Events

Listen for events from child components:

```blade
<livewire:create-post />
```

```php
#[On('post-created')]
public function refreshList()
{
    $this->posts = Post::latest()->get();
}
```

### JavaScript Interaction

Dispatch events from JavaScript:

```javascript
$wire.dispatch("post-created", { title: "New Post" });
```

Listen in JavaScript:

```javascript
Livewire.on("post-created", (data) => {
    console.log("Post created:", data);
});
```

### Alpine Events

Dispatch Alpine events:

```blade
<div x-data @post-created.window="handlePostCreated()">
    <!-- Content -->
</div>
```

### Direct Dispatching

Dispatch directly to a component:

```php
$this->dispatch('post-created')->to('post-list');
```

### Testing Events

Test events in tests:

```php
Livewire::test(CreatePost::class)
    ->call('save')
    ->assertDispatched('post-created');
```

### Laravel Echo Integration

Integrate with Laravel Echo for real-time events:

```php
use Livewire\Attributes\On;

#[On('echo:channel-name,EventName')]
public function handleBroadcast($event)
{
    // Handle broadcast event
}
```

## Lifecycle Hooks

Lifecycle hooks allow you to run code at specific points in a component's lifecycle.

### Mount

The `mount()` method runs when a component is first created:

```php
public function mount(User $user)
{
    $this->user = $user;
    $this->loadPosts();
}
```

### Boot

The `boot()` method runs on every request:

```php
public function boot()
{
    // Runs on every request
}
```

### Update

The `updated()` method runs when a property is updated:

```php
public function updated($propertyName)
{
    if ($propertyName === 'search') {
        $this->resetPage();
    }
}
```

Or for specific properties:

```php
public function updatedSearch()
{
    $this->resetPage();
}
```

### Hydrate

The `hydrate()` method runs when a component is hydrated from storage:

```php
public function hydrate()
{
    // Runs when component is hydrated
}
```

### Dehydrate

The `dehydrate()` method runs when a component is dehydrated for storage:

```php
public function dehydrate()
{
    // Runs when component is dehydrated
}
```

### Render

The `render()` method is called to render the component:

```php
public function render()
{
    return view('livewire.posts.index', [
        'posts' => Post::latest()->get(),
    ]);
}
```

### Exception

The `exception()` method handles exceptions:

```php
public function exception($exception, $stopPropagation = false)
{
    // Handle exception
}
```

### Trait Hooks

Traits can define lifecycle hooks:

```php
trait HasPosts
{
    public function bootHasPosts()
    {
        // Runs when component boots
    }
}
```

### Form Object Hooks

Form objects can have lifecycle hooks:

```php
class PostForm extends Form
{
    public function mount(Post $post)
    {
        $this->title = $post->title;
        $this->content = $post->content;
    }
}
```

## Nesting Components

Components can be nested to create complex UIs.

### Independent Nature

Nested components are independent by default:

```blade
<livewire:post-list />
<livewire:post-form />
```

### Passing Props

Pass props to nested components:

```blade
<livewire:post-item :post="$post" />
```

### Loops

Use components in loops:

```blade
@foreach ($posts as $post)
    <livewire:post-item :post="$post" wire:key="post-{{ $post->id }}" />
@endforeach
```

### Reactive Props

Props are reactive by default:

```blade
<livewire:post-item :post="$post" />
```

When `$post` changes, the component updates automatically.

### wire:model Binding

Bind `wire:model` across component boundaries:

```blade
<livewire:post-form />
```

```php
// In post-form component
public string $title = '';
```

```blade
<!-- In parent -->
<input wire:model="title">
```

### Slots

Pass content to components using slots:

```blade
<livewire:modal>
    <x-slot:title>Delete Post</x-slot:title>
    <p>Are you sure?</p>
</livewire:modal>
```

### HTML Attributes

Pass HTML attributes:

```blade
<livewire:button class="btn-primary" data-id="123">
    Click Me
</livewire:button>
```

### Islands vs. Nested Components

Islands are isolated regions that update independently:

```blade
@island(name: 'stats')
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

Nested components are full components with their own lifecycle.

### Event Communication

Components communicate via events:

```php
// Child component
$this->dispatch('post-created', id: $this->postId);

// Parent component
#[On('post-created')]
public function refreshList($id)
{
    // Handle event
}
```

### Direct Parent Access

Access parent component:

```php
$parent = $this->getParent();
```

### Dynamic/Recursive Components

Create dynamic components:

```blade
<livewire:dynamic-component :component="$componentName" :props="$props" />
```

### Forcing Re-render

Force a component to re-render:

```php
$this->dispatch('$refresh');
```

## Testing

Livewire components can be tested using Pest or PHPUnit.

### Pest Testing

Test components with Pest:

```php
use Livewire\Volt\Volt;

test('counter increments', function () {
    Volt::test('counter')
        ->assertSee('Count: 0')
        ->call('increment')
        ->assertSee('Count: 1');
});
```

### Browser Testing

Test with browser:

```php
it('can create a post', function () {
    $this->actingAs(User::factory()->create());

    $page = visit('/posts/create');

    $page->fill('title', 'My Post')
         ->fill('content', 'Post content')
         ->click('Save')
         ->assertSee('Post created');
});
```

### Views

Test component views:

```php
Livewire::test(CreatePost::class)
    ->assertSee('Create Post')
    ->assertSee('Title');
```

### Authentication

Test authenticated components:

```php
Livewire::test(CreatePost::class)
    ->actingAs($user)
    ->assertSee('Create Post');
```

### Properties

Test properties:

```php
Livewire::test(CreatePost::class)
    ->assertSet('title', '')
    ->set('title', 'My Post')
    ->assertSet('title', 'My Post');
```

### Actions

Test actions:

```php
Livewire::test(CreatePost::class)
    ->set('title', 'My Post')
    ->set('content', 'Content')
    ->call('save')
    ->assertHasNoErrors();
```

### Validation

Test validation:

```php
Livewire::test(CreatePost::class)
    ->set('title', '')
    ->call('save')
    ->assertHasErrors(['title' => 'required']);
```

### Authorization

Test authorization:

```php
Livewire::test(DeletePost::class, ['post' => $post])
    ->call('delete')
    ->assertForbidden();
```

### Redirects

Test redirects:

```php
Livewire::test(CreatePost::class)
    ->call('save')
    ->assertRedirect('/posts');
```

### Events

Test events:

```php
Livewire::test(CreatePost::class)
    ->call('save')
    ->assertDispatched('post-created');
```

### PHPUnit

Test with PHPUnit:

```php
use Tests\TestCase;
use Livewire\Livewire;

class CreatePostTest extends TestCase
{
    public function test_can_create_post()
    {
        Livewire::test(CreatePost::class)
            ->set('title', 'My Post')
            ->call('save')
            ->assertHasNoErrors();
    }
}
```

## AlpineJS Integration

Livewire includes Alpine.js and provides seamless integration.

### x-data

Use `x-data` with Livewire:

```blade
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open">Content</div>
</div>
```

### x-text

Use `x-text` with Livewire properties:

```blade
<div x-text="$wire.title"></div>
```

### x-on

Listen for events:

```blade
<button @click="$wire.save()">Save</button>
```

### $wire Object

The `$wire` object provides access to Livewire from Alpine:

**Properties:**

```javascript
$wire.title = "New Title";
let title = $wire.title;
```

**Methods:**

```javascript
$wire.save();
$wire.delete(123);
```

**Refresh:**

```javascript
$wire.$refresh();
```

**Dispatch:**

```javascript
$wire.$dispatch("event-name", { data: "value" });
```

**On:**

```javascript
$wire.$on("event-name", (data) => {
    console.log(data);
});
```

**El:**

```javascript
let element = $wire.$el;
```

**Get:**

```javascript
let value = $wire.$get("property");
```

**Set:**

```javascript
$wire.$set("property", "value");
```

**Toggle:**

```javascript
$wire.$toggle("property");
```

**Call:**

```javascript
$wire.$call("method", arg1, arg2);
```

**JS:**

```javascript
$wire.$js.methodName = () => {
    // Custom JavaScript method
};
```

**Entangle:**

```javascript
Alpine.data("component", () => ({
    title: $wire.$entangle("title"),
}));
```

**Watch:**

```javascript
$wire.$watch("title", (value) => {
    console.log("Title changed:", value);
});
```

**Upload:**

```javascript
$wire.$upload("photo", file, (progress) => {
    console.log("Progress:", progress);
});
```

**Intercept:**

```javascript
$wire.$intercept("save", ({ component, params, preventDefault }) => {
    // Intercept and modify
});
```

### Manual Bundling

If you need to bundle Alpine manually:

```javascript
import Alpine from "alpinejs";
import Livewire from "@livewire/livewire";

Alpine.plugin(Livewire);
Alpine.start();
```

## Navigation

Livewire provides SPA-like navigation with `wire:navigate`.

### wire:navigate

Use `wire:navigate` for instant navigation:

```blade
<a href="/posts" wire:navigate>Posts</a>
```

### Redirects

Redirect in actions:

```php
public function save()
{
    // Save logic

    return $this->redirect('/posts');
}
```

### Prefetching

Prefetch pages on hover:

```blade
<a href="/posts" wire:navigate.hover>Posts</a>
```

### @persist

Persist elements across navigation:

```blade
@persist('sidebar')
    <div class="sidebar">
        <!-- Sidebar content -->
    </div>
@endpersist
```

### Active Links

Show active state:

```blade
<a href="/posts" wire:navigate class="{{ request()->is('posts*') ? 'active' : '' }}">
    Posts
</a>
```

### Scroll Position

Preserve scroll position:

```blade
<div wire:navigate:scroll>
    <!-- Scrollable content -->
</div>
```

### JavaScript Hooks

Hook into navigation:

```javascript
document.addEventListener("livewire:navigated", () => {
    console.log("Navigation completed");
});
```

### Manual Navigation

Navigate programmatically:

```php
$this->redirect('/posts');
```

Or from JavaScript:

```javascript
$wire.$redirect("/posts");
```

### Analytics

Track navigation:

```javascript
document.addEventListener("livewire:navigated", (event) => {
    // Track page view
    gtag("config", "GA_MEASUREMENT_ID", {
        page_path: event.detail.url,
    });
});
```

### Script Evaluation

Scripts are evaluated on navigation:

```blade
<script>
    console.log('Page loaded');
</script>
```

### Progress Bar Customization

Customize the progress bar:

```css
[wire\:navigate] {
    /* Custom styles */
}
```

## Islands

Islands are isolated regions within a component that update independently.

### @island Directive

Create an island:

```blade
@island(name: 'stats')
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Lazy Loading

Load islands lazily:

```blade
@island(name: 'stats', lazy: true)
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Deferred Loading

Defer island loading:

```blade
@island(name: 'stats', defer: true)
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Custom Placeholders

Custom placeholder:

```blade
@island(name: 'stats', lazy: true)
    <x-slot:placeholder>
        <div>Loading stats...</div>
    </x-slot:placeholder>
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Named Islands

Reference islands by name:

```blade
@island(name: 'stats')
    <!-- Content -->
@endisland

<button wire:click="refresh" wire:island="stats">Refresh</button>
```

### Append/Prepend Modes

Append or prepend content:

```blade
<button wire:click="loadMore" wire:island="stats" wire:append>Load More</button>
```

### Nested Islands

Nest islands:

```blade
@island(name: 'parent')
    @island(name: 'child')
        <!-- Content -->
    @endisland
@endisland
```

### Always Render

Always render island:

```blade
@island(name: 'stats', always: true)
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Skip Initial Render

Skip initial render:

```blade
@island(name: 'stats', skip: true)
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Polling

Poll islands:

```blade
@island(name: 'stats', poll: '5s')
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Data/Loop/Conditional Scope

Use islands in loops:

```blade
@foreach ($posts as $post)
    @island(name: "post-{$post->id}")
        <div>{{ $post->title }}</div>
    @endisland
@endforeach
```

## Lazy Loading

Lazy loading defers component rendering until needed.

### lazy vs defer

-   **lazy**: Loads when component enters viewport
-   **defer**: Loads after page load

```blade
<livewire:revenue lazy />
<livewire:expenses defer />
```

### Basic Usage

```blade
<livewire:revenue lazy />
```

Or in PHP:

```php
use Livewire\Attributes\Lazy;

#[Lazy]
class Revenue extends Component
{
    // Component code
}
```

### Placeholder HTML

Custom placeholder:

```blade
<livewire:revenue lazy>
    <x-slot:placeholder>
        <div>Loading revenue...</div>
    </x-slot:placeholder>
</livewire:revenue>
```

### Immediate Loading

Load immediately:

```blade
<livewire:revenue lazy.immediate />
```

### Props

Pass props to lazy components:

```blade
<livewire:revenue :year="$year" lazy />
```

### Enforcing Defaults

Enforce default props:

```blade
<livewire:revenue :year="2024" lazy />
```

### Bundling

Bundle lazy components:

```blade
<livewire:revenue lazy.bundle />
<livewire:expenses defer.bundle />
```

### Full-Page Loading

Lazy load full pages:

```php
Route::livewire('/dashboard', Dashboard::class)->lazy();
```

### Default Placeholder

Set default placeholder in config:

```php
'component_placeholder' => 'livewire.placeholder',
```

### Disabling for Tests

Disable lazy loading in tests:

```php
Livewire::withoutLazyLoading();
```

## Loading States

Show loading states during Livewire requests.

### data-loading Attribute

Every element that triggers a request gets `data-loading`:

```blade
<button wire:click="save" class="data-loading:opacity-50">
    Save
</button>
```

### wire:loading Directive

Show content during loading:

```blade
<button wire:click="save">
    <span wire:loading.remove>Save</span>
    <span wire:loading>Saving...</span>
</button>
```

### Basic Usage

```blade
<div wire:loading>Loading...</div>
```

### Styling with Tailwind/CSS

Style with Tailwind:

```blade
<button wire:click="save" class="data-loading:opacity-50 data-loading:pointer-events-none">
    Save
</button>
```

Or with CSS:

```css
[data-loading] {
    opacity: 0.5;
    pointer-events: none;
}
```

### Advantages

-   Automatic: No manual state management
-   Performant: Uses CSS instead of JavaScript
-   Accessible: Works with screen readers

### Delays

Add delays:

```blade
<div wire:loading.delay>Loading...</div>
<div wire:loading.delay.shortest>Loading...</div>
```

### Targets

Target specific actions:

```blade
<div wire:loading wire:target="save">Saving...</div>
<div wire:loading wire:target="delete">Deleting...</div>
```

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

## File Uploads

Handle file uploads in Livewire components.

### WithFileUploads Trait

Use the trait:

```php
use Livewire\WithFileUploads;
use Livewire\Component;

class UploadPhoto extends Component
{
    use WithFileUploads;

    public $photo;
}
```

### wire:model on File Inputs

Bind to file input:

```blade
<input type="file" wire:model="photo">
```

### Storing

Store uploaded files:

```php
public function save()
{
    $this->validate([
        'photo' => 'image|max:1024',
    ]);

    $path = $this->photo->store('photos');
}
```

### Multiple Files

Upload multiple files:

```php
public $photos = [];
```

```blade
<input type="file" wire:model="photos" multiple>
```

### Validation

Validate files:

```php
$this->validate([
    'photo' => 'required|image|max:1024|mimes:jpeg,png',
]);
```

### Temporary Preview URLs

Preview before storing:

```blade
@if ($photo)
    <img src="{{ $photo->temporaryUrl() }}">
@endif
```

### Testing

Test file uploads:

```php
Livewire::test(UploadPhoto::class)
    ->set('photo', UploadedFile::fake()->image('photo.jpg'))
    ->call('save')
    ->assertHasNoErrors();
```

### S3 Direct Upload

Upload directly to S3:

```php
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

$path = $this->photo->storeAs('photos', 'photo.jpg', 's3');
```

### Loading/Progress Indicators

Show progress:

```blade
<div wire:loading wire:target="photo">Uploading...</div>
<input type="file" wire:model="photo">
```

### Cancelling

Cancel upload:

```php
$this->photo->delete();
```

### JavaScript API

Upload from JavaScript:

```javascript
$wire.$upload("photo", file, (progress) => {
    console.log("Progress:", progress);
});
```

### Configuration

Configure in `config/livewire.php`:

```php
'file_uploads' => [
    'disk' => 'local',
    'directory' => 'livewire-tmp',
],
```

## Pagination

Paginate data in Livewire components.

### WithPagination Trait

Use the trait:

```php
use Livewire\WithPagination;
use Livewire\Component;

class PostList extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.post-list', [
            'posts' => Post::paginate(10),
        ]);
    }
}
```

### Basic Usage

Display pagination links:

```blade
<div>
    @foreach ($posts as $post)
        <div>{{ $post->title }}</div>
    @endforeach

    {{ $posts->links() }}
</div>
```

### URL Query String Tracking

Track page in URL:

```php
use Livewire\WithPagination;

class PostList extends Component
{
    use WithPagination;

    protected $paginationQueryString = ['page'];
}
```

### Scroll Behavior

Reset scroll on page change:

```blade
<div wire:poll.5s>
    {{ $posts->links() }}
</div>
```

### Resetting Page

Reset page when filters change:

```php
public function updatedSearch()
{
    $this->resetPage();
}
```

### Multiple Paginators

Use multiple paginators:

```php
public function render()
{
    return view('livewire.dashboard', [
        'posts' => Post::paginate(10, ['*'], 'postsPage'),
        'users' => User::paginate(10, ['*'], 'usersPage'),
    ]);
}
```

### Hooks

Use pagination hooks:

```php
public function updatingPage($page)
{
    // Before page changes
}
```

### Simple/Cursor Pagination

Use simple pagination:

```php
$posts = Post::simplePaginate(10);
```

Or cursor pagination:

```php
$posts = Post::cursorPaginate(10);
```

### Bootstrap/Tailwind Themes

Use different themes:

```blade
{{ $posts->links('pagination::bootstrap-4') }}
{{ $posts->links('pagination::tailwind') }}
```

### Custom Views

Create custom pagination view:

```blade
{{ $posts->links('livewire.custom-pagination') }}
```

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

## File Downloads

Handle file downloads in Livewire.

### Standard Laravel Responses

Return download response:

```php
public function download()
{
    return Storage::download('file.pdf');
}
```

### Streaming

Stream large files:

```php
public function download()
{
    return Storage::response('large-file.zip');
}
```

### Testing

Test downloads:

```php
Livewire::test(DownloadFile::class)
    ->call('download')
    ->assertFileDownloaded('file.pdf');
```

## Teleport

Teleport allows you to render component content in a different part of the DOM.

### @teleport Directive

Teleport content:

```blade
@teleport('#modal-container')
    <div class="modal">
        <!-- Modal content -->
    </div>
@endteleport
```

### Basic Usage

```blade
<div id="modal-root"></div>

@teleport('#modal-root')
    <div class="modal">Content</div>
@endteleport
```

### Why Use

-   Render modals at root level
-   Avoid z-index issues
-   Better accessibility

### Common Use Cases

-   Modals
-   Dropdowns
-   Tooltips
-   Notifications

### Constraints

-   Target must exist in DOM
-   Only one target per teleport
-   Content is moved, not copied

### Alpine Integration

Use with Alpine:

```blade
<div x-data="{ open: false }">
    <button @click="open = true">Open</button>

    @teleport('body')
        <div x-show="open" class="modal">
            Content
        </div>
    @endteleport
</div>
```

## Morphing

Morphing is Livewire's algorithm for updating the DOM efficiently.

### How It Works

Livewire compares the old and new HTML and updates only what changed.

### Shortcomings

-   Can't morph certain elements (scripts, styles)
-   Requires `wire:key` in loops
-   May have issues with third-party libraries

### Internal Look-Ahead

Livewire uses look-ahead to optimize morphing.

### Morph Markers

Use morph markers:

```blade
<div wire:key="unique-id">
    <!-- Content -->
</div>
```

### Wrapping Conditionals

Wrap conditionals:

```blade
<div wire:key="conditional-{{ $condition }}">
    @if ($condition)
        <!-- Content -->
    @endif
</div>
```

### wire:replace

Replace entire element:

```blade
<div wire:replace>
    <!-- Entire div is replaced -->
</div>
```

## Hydration

Hydration is the process of restoring component state from storage.

### Dehydrating HTML/JSON Snapshot

Livewire dehydrates component state into HTML and JSON:

```html
<div wire:id="abc123">
    <!-- HTML -->
</div>

<script>
    window.Livewire.find("abc123").__instance = {
        /* JSON snapshot */
    };
</script>
```

### Hydrating

On subsequent requests, Livewire hydrates the component from the snapshot.

### Advanced Hydration with Tuples/Metadata

Store additional metadata:

```php
public function dehydrate(): array
{
    return [
        'data' => $this->data,
        'metadata' => $this->metadata,
    ];
}
```

### Custom Property Types with Synthesizers

Use synthesizers for custom types (see [Synthesizers](#synthesizers) section).

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

## JavaScript

Livewire provides extensive JavaScript APIs.

### Script Execution

Scripts in components are executed:

```blade
<script>
    console.log('Component loaded');
</script>
```

### $wire Object

Access component from JavaScript (see [AlpineJS Integration](#alpinejs-integration) section).

### Loading Assets @assets

Load assets in components:

```blade
@assets
    <link rel="stylesheet" href="/custom.css">
    <script src="/custom.js"></script>
@endassets
```

### Interceptors

Intercept Livewire operations:

**Component Interceptor:**

```javascript
Livewire.hook("component.init", ({ component, cleanup }) => {
    console.log("Component initialized:", component);
});
```

**Message Interceptor:**

```javascript
Livewire.hook("message.processed", ({ message, component }) => {
    console.log("Message processed:", message);
});
```

**Request Interceptor:**

```javascript
Livewire.hook("request", ({ payload, respond, preventDefault }) => {
    // Modify request
    respond(({ status, response }) => {
        // Handle response
    });
});
```

### Global Livewire Events

Listen for global events:

```javascript
document.addEventListener("livewire:init", () => {
    console.log("Livewire initialized");
});

document.addEventListener("livewire:navigated", () => {
    console.log("Navigation completed");
});
```

### Livewire Global Object

Access Livewire globally:

```javascript
Livewire.find("component-id");
Livewire.all();
Livewire.dispatch("event-name");
```

### Livewire.hook()

Hook into Livewire lifecycle:

```javascript
Livewire.hook("morph", ({ el, component, skip }) => {
    // Custom morphing logic
});
```

### Custom Directives

Create custom directives:

```javascript
Livewire.directive("custom", (el, directive, component) => {
    // Custom directive logic
});
```

### Server-Side JS Evaluation

Evaluate JavaScript from server:

```php
$this->js('console.log("Hello from server")');
```

### Common Patterns

**Debouncing:**

```javascript
let timeout;
$wire.$watch("search", (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        // Perform search
    }, 300);
});
```

**Polling:**

```blade
<div wire:poll.5s>
    <!-- Content -->
</div>
```

### Best Practices

-   Use `wire:key` in loops
-   Debounce expensive operations
-   Use `wire:loading` for feedback
-   Validate on server
-   Authorize actions

### Debugging

Enable debugging:

```javascript
window.Livewire = {
    ...window.Livewire,
    debug: true,
};
```

### $wire Reference

See [AlpineJS Integration](#alpinejs-integration) for `$wire` API.

### snapshot Object

Access component snapshot:

```javascript
let snapshot = $wire.$snapshot;
```

### component Object

Access component object:

```javascript
let component = $wire.$component;
```

### message Payload

Access message payload:

```javascript
Livewire.hook("message.processed", ({ message }) => {
    console.log(message.payload);
});
```

## Troubleshooting

Common issues and solutions.

### Component Mismatches

**Problem:** Component HTML doesn't match expected structure.

**Solution:** Ensure single root element and proper `wire:key` usage.

### wire:key

**Problem:** Components not updating correctly in loops.

**Solution:** Always use `wire:key` in loops:

```blade
@foreach ($items as $item)
    <div wire:key="item-{{ $item->id }}">{{ $item->name }}</div>
@endforeach
```

### Duplicate Keys

**Problem:** Duplicate `wire:key` values.

**Solution:** Ensure unique keys:

```blade
<div wire:key="post-{{ $post->id }}-{{ $post->updated_at }}">
```

### Multiple Alpine Instances

**Problem:** Multiple Alpine instances conflicting.

**Solution:** Livewire includes Alpine. Don't include it separately.

### Missing @alpinejs/ui

**Problem:** Alpine UI plugins not working.

**Solution:** Include Alpine UI if needed:

```javascript
import Alpine from "alpinejs";
import ui from "@alpinejs/ui";
Alpine.plugin(ui);
```

## Security

Livewire provides several security features.

### Authorizing Action Parameters/Public Properties

Always authorize:

```php
public function delete(int $id)
{
    $post = Post::findOrFail($id);
    $this->authorize('delete', $post);
    $post->delete();
}
```

### Model Properties

Protect model properties:

```php
#[Locked]
public Post $post;
```

### #[Locked] Attribute

Prevent frontend modification:

```php
use Livewire\Attributes\Locked;

#[Locked]
public string $secret = 'my-secret';
```

### Middleware Persistence

Middleware runs on every request:

```php
public function boot()
{
    $this->middleware('auth');
}
```

### Snapshot Checksums

Livewire validates snapshot checksums to prevent tampering.

## CSP

Content Security Policy support in Livewire.

### CSP-Safe Build

Use CSP-safe build:

```javascript
import { Livewire } from "@livewire/livewire/csp";
```

### What's Supported/Not

**Supported:**

-   Basic directives
-   Event handling
-   Form submission

**Not Supported:**

-   Complex JavaScript expressions in directives
-   Inline event handlers

### Headers

Set CSP headers:

```php
return response()->view('app')
    ->header('Content-Security-Policy', "default-src 'self'");
```

### Performance

CSP mode has minimal performance impact.

### Testing

Test CSP compliance:

```php
Livewire::test(Component::class)
    ->assertCspCompliant();
```

## Routing

For full-page components, use `Route::livewire()`:

```php
// Recommended for all component types
Route::livewire('/dashboard', Dashboard::class);

// For view-based components, you can use the component name
Route::livewire('/dashboard', 'pages::dashboard');
```

Using `Route::livewire()` is now the preferred method and is required for single-file and multi-file components to work correctly as full-page components.

## Layout Configuration

By default, Livewire looks for a layout at `resources/views/layouts/app.blade.php`. You can create this file by running:

```bash
php artisan livewire:layout
```

The layout should include `@livewireStyles` in the `<head>` and `@livewireScripts` before `</body>`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body>
        {{ $slot }}

        @livewireScripts
    </body>
</html>
```

## Configuration

### Config File Updates

Several configuration keys have been renamed in v4:

**Layout configuration:**

```php
// Before (v3)
'layout' => 'components.layouts.app',

// After (v4)
'component_layout' => 'layouts::app',
```

**Placeholder configuration:**

```php
// Before (v3)
'lazy_placeholder' => 'livewire.placeholder',

// After (v4)
'component_placeholder' => 'livewire.placeholder',
```

### New Configuration Options

**Component locations:**

```php
'component_locations' => [
    resource_path('views/components'),
    resource_path('views/livewire'),
],
```

**Component namespaces:**

```php
'component_namespaces' => [
    'layouts' => resource_path('views/layouts'),
    'pages' => resource_path('views/pages'),
],
```

**Make command defaults:**

```php
'make_command' => [
    'type' => 'sfc',  // Options: 'sfc', 'mfc', or 'class'
    'emoji' => true,   // Whether to use ⚡ emoji prefix
],
```

**CSP-safe mode:**

```php
'csp_safe' => false,
```

Enable Content Security Policy mode to avoid unsafe-eval violations. When enabled, Livewire uses the Alpine CSP build. Note: This mode restricts complex JavaScript expressions in directives.

## Key Features

### Islands

Islands allow you to create isolated regions within a component that update independently, dramatically improving performance without creating separate child components.

```blade
@island(name: 'stats', lazy: true)
    <div>{{ $this->expensiveStats }}</div>
@endisland
```

### Loading Improvements

**Deferred loading:**

```blade
<livewire:revenue defer />
```

```php
#[Defer]
class Revenue extends Component { ... }
```

**Bundled loading:**

```blade
<livewire:revenue lazy.bundle />
<livewire:expenses defer.bundle />
```

```php
#[Lazy(bundle: true)]
class Revenue extends Component { ... }
```

### Async Actions

Run actions in parallel without blocking other requests:

```blade
<button wire:click.async="logActivity">Track</button>
```

```php
#[Async]
public function logActivity() { ... }
```

## New Directives and Modifiers

### wire:sort - Drag-and-Drop Sorting

Built-in support for sortable lists with drag-and-drop:

```blade
<ul wire:sort="updateOrder">
    @foreach ($items as $item)
        <li wire:sort:item="{{ $item->id }}" wire:key="{{ $item->id }}">{{ $item->name }}</li>
    @endforeach
</ul>
```

### wire:intersect - Viewport Intersection

Run actions when elements enter or leave the viewport:

```blade
<!-- Basic usage -->
<div wire:intersect="loadMore">...</div>

<!-- With modifiers -->
<div wire:intersect.once="trackView">...</div>
<div wire:intersect:leave="pauseVideo">...</div>
<div wire:intersect.half="loadMore">...</div>
<div wire:intersect.full="startAnimation">...</div>

<!-- With options -->
<div wire:intersect.margin.200px="loadMore">...</div>
<div wire:intersect.threshold.50="trackScroll">...</div>
```

Available modifiers:

-   `.once` - Fire only once
-   `.half` - Wait until half is visible
-   `.full` - Wait until fully visible
-   `.threshold.X` - Custom visibility percentage (0-100)
-   `.margin.Xpx` or `.margin.X%` - Intersection margin

### wire:ref - Element References

Easily reference and interact with elements in your template:

```blade
<div wire:ref="modal">
    <!-- Modal content -->
</div>

<button wire:click="$js.scrollToModal">Scroll to modal</button>

<script>
    this.$js.scrollToModal = () => {
        this.$refs.modal.scrollIntoView()
    }
</script>
```

### .renderless Modifier

Skip component re-rendering directly from the template:

```blade
<button wire:click.renderless="trackClick">Track</button>
```

### .preserve-scroll Modifier

Preserve scroll position during updates to prevent layout jumps:

```blade
<button wire:click.preserve-scroll="loadMore">Load More</button>
```

### data-loading Attribute

Every element that triggers a network request automatically receives a `data-loading` attribute, making it easy to style loading states with Tailwind:

```blade
<button wire:click="save" class="data-loading:opacity-50 data-loading:pointer-events-none">
    Save Changes
</button>
```

## JavaScript Improvements

### $errors Magic Property

Access your component's error bag from JavaScript:

```blade
<div wire:show="$errors.has('email')">
    <span wire:text="$errors.first('email')"></span>
</div>
```

### $intercept Magic

Intercept and modify Livewire requests from JavaScript:

```blade
<script>
this.$intercept('save', ({ ... }) => {
    // ...
})
</script>
```

### Island Targeting from JavaScript

Trigger island renders directly from the template:

```blade
<button wire:click.append="loadMore" wire:island="stats">
    Load more
</button>
```

## JavaScript in View-Based Components

View-based components can now include `<script>` tags without the `@script` wrapper. These scripts are served as separate cached files for better performance and automatic `$wire` binding:

```blade
<div>
    <!-- Your component template -->
</div>

<script>
    // $wire is automatically bound as 'this'
    this.count++  // Same as $wire.count++

    // $wire is still available if preferred
    $wire.save()
</script>
```

## Upgrading from Volt

Livewire v4 now supports single-file components, which use the same syntax as Volt class-based components. This means you can migrate from Volt to Livewire's built-in single-file components.

### Update Component Imports

Replace all instances of `Livewire\Volt\Component` with `Livewire\Component`:

```php
// Before (Volt)
use Livewire\Volt\Component;

new class extends Component { ... }

// After (Livewire v4)
use Livewire\Component;

new class extends Component { ... }
```

### Remove Volt Service Provider

Delete the Volt service provider file:

```bash
rm app/Providers/VoltServiceProvider.php
```

Then remove it from the providers array in `bootstrap/providers.php`.

### Remove Volt Package

Uninstall the Volt package:

```bash
composer remove livewire/volt
```

## Performance Improvements

Livewire v4 includes significant performance improvements:

-   **Non-blocking polling**: `wire:poll` no longer blocks other requests or is blocked by them
-   **Parallel live updates**: `wire:model.live` requests now run in parallel, allowing faster typing and quicker results

These improvements happen automatically—no changes needed to your code.

## Breaking Changes

### Method Signature Changes

**Streaming:**

```php
// Before (v3)
$this->stream(to: '#container', content: 'Hello', replace: true);

// After (v4)
$this->stream(content: 'Hello', replace: true, el: '#container');
```

### JavaScript Deprecations

**Deprecated: $wire.$js() method**

```javascript
// Deprecated (v3)
$wire.$js("bookmark", () => {
    // Toggle bookmark...
});

// New (v4)
$wire.$js.bookmark = () => {
    // Toggle bookmark...
};
```

**Deprecated: commit and request hooks**

The commit and request hooks have been deprecated in favor of a new interceptor system. See the JavaScript Interceptors documentation for migration details.

### Use wire:navigate:scroll

When using `wire:scroll` to preserve scroll in a scrollable container across `wire:navigate` requests in v3, you will need to instead use `wire:navigate:scroll` in v4:

```blade
@persist('sidebar')
    <div class="overflow-y-scroll" wire:navigate:scroll>
        <!-- ... -->
    </div>
@endpersist
```

## Best Practices

1. **Single root element**: Components must have exactly one root HTML element
2. **Use wire:loading**: Add loading states for better UX
3. **Use wire:key**: Always add `wire:key` in loops
4. **Use wire:model.live**: For real-time updates (deferred by default in v3, but can be made live)
5. **Prefer lifecycle hooks**: Use `mount()`, `updatedFoo()` for initialization and reactive side effects
6. **Validate form data**: Always validate form data in Livewire actions
7. **Run authorization checks**: Always run authorization checks in Livewire actions

## Resources

-   [Livewire Documentation](https://livewire.laravel.com/docs)
-   [Livewire v4 Upgrade Guide](https://livewire.laravel.com/docs/upgrade)
-   [GitHub Repository](https://github.com/livewire/livewire)

## Notes

-   Livewire v4 is currently in beta - test thoroughly before production use
-   Most applications can upgrade to v4 with minimal changes
-   Breaking changes are primarily configuration updates and method signature changes
-   Performance improvements happen automatically
