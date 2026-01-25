# Unified Create/Edit Pattern

> **Purpose**: Guide for consolidating separate create and edit pages into a single unified component that handles both modes.

## Overview

This pattern eliminates code duplication by merging create and edit workflows into a single Livewire component and Blade template. The component detects the mode based on whether a model is provided and adjusts its behavior accordingly.

## When to Apply

Use this pattern when you have:
- ✅ Separate create and edit pages with duplicate code
- ✅ Similar forms/fields for both modes
- ✅ Same validation rules (or mostly the same)
- ✅ Redundant navigation and UI elements

**Don't use when**:
- ❌ Create and edit workflows are fundamentally different
- ❌ Different permissions/authorization for each mode
- ❌ Significantly different form fields

---

## Step-by-Step Implementation

### Step 1: Update Component Signature

Make the model parameter optional in `mount()`:

```php
// Before (edit-only)
public function mount(Model $model): void
{
    $this->authorize(Permissions::EDIT());
    $this->model = $model;
}

// After (unified)
public function mount(?Model $model = null): void
{
    $this->authorizeAccess($model);
    $this->initializeModel($model);
}
```

### Step 2: Add Mode Tracking

Add a locked property to track create vs edit mode:

```php
#[Locked]
public bool $isCreateMode = true;
```

### Step 3: Break Down mount() (SRP)

Split `mount()` into single-purpose methods:

```php
protected function authorizeAccess(?Model $model): void
{
    $permission = $model 
        ? Permissions::EDIT() 
        : Permissions::CREATE();
    
    $this->authorize($permission);
}

protected function initializeModel(?Model $model): void
{
    if ($model) {
        $this->loadExistingModel($model);
        return;
    }
    
    $this->prepareNewModel();
}

protected function loadExistingModel(Model $model): void
{
    $this->model = $model->load(['relations']);
    $this->isCreateMode = false;
    $this->fillFromModel();
}

protected function prepareNewModel(): void
{
    $this->model = new Model();
    // Set defaults from query params if needed
    // e.g., $this->type = request()->query('type');
}
```

### Step 4: Add create() Method

Implement `create()` alongside existing `save()`:

```php
public function create(): void
{
    $this->validate();

    $data = $this->prepareData();
    $model = Model::create($data);

    $this->sendSuccessNotification($model, 'pages.common.create.success');
    $this->redirectAfterCreate($model);
}

public function save(): void
{
    $this->validate();

    $data = $this->prepareData();
    $this->model->update($data);

    $this->sendSuccessNotification($this->model, 'pages.common.edit.success');
    $this->redirect(route('models.show', $this->model), navigate: true);
}
```

### Step 5: Extract Helper Methods (SRP)

Create small, focused helper methods:

```php
protected function prepareData(): array
{
    // Common data preparation logic
    return [
        'name' => $this->name,
        'description' => $this->description,
        // ... other fields
    ];
}

protected function sendSuccessNotification(Model $model, string $messageKey): void
{
    NotificationBuilder::make()
        ->title($messageKey, ['name' => $model->label()])
        ->success()
        ->persist()
        ->send();
}

protected function redirectAfterCreate(Model $model): void
{
    // Custom redirect logic for create
    $this->redirect(route('models.show', $model), navigate: true);
}
```

### Step 6: Add Computed Properties (Clean Blade)

Move all conditional logic from Blade to PHP:

```php
// Computed properties for clean Blade templates
public function getPageTitleProperty(): string
{
    return $this->isCreateMode 
        ? __('pages.common.create.title', ['type' => __('types.model')])
        : __('pages.common.edit.title', ['type' => __('types.model')]);
}

public function getSubmitButtonTextProperty(): string
{
    return $this->isCreateMode 
        ? __('pages.common.create.submit') 
        : __('pages.common.edit.submit');
}

public function getSubmitActionProperty(): string
{
    return $this->isCreateMode ? 'create' : 'save';
}

public function getCancelUrlProperty(): string
{
    return $this->isCreateMode
        ? route('models.index')
        : route('models.show', $this->model);
}
```

### Step 7: Update Blade Template

Use computed properties instead of inline conditionals:

```blade
{{-- Before: Logic in Blade --}}
<x-ui.title>
    {{ $isCreateMode 
        ? __('pages.common.create.title', ['type' => ...])
        : __('pages.common.edit.title', ['type' => ...]) 
    }}
</x-ui.title>

<x-ui.form wire:submit="{{ $isCreateMode ? 'create' : 'save' }}">
    <x-ui.button type="submit">
        {{ $isCreateMode ? __('actions.create') : __('actions.save') }}
    </x-ui.button>
</x-ui.form>

{{-- After: Clean Blade --}}
<x-ui.title>{{ $this->pageTitle }}</x-ui.title>

<x-ui.form wire:submit="{{ $this->submitAction }}">
    <x-ui.button type="submit">
        {{ $this->submitButtonText }}
    </x-ui.button>
</x-ui.form>
```

### Step 8: Update Routes

Change routes to use optional parameters:

```php
// Before: Separate routes
Route::livewire('/create', 'pages::models.create')
    ->name('models.create');
Route::livewire('/{model}/edit', 'pages::models.edit')
    ->name('models.edit');

// After: Unified routes
Route::livewire('/settings/{model?}', 'pages::models.edit-settings')
    ->name('models.settings.edit');
```

### Step 9: Update Navigation Links

Update all route references:

```blade
{{-- Before --}}
<x-ui.button href="{{ route('models.create') }}">Create</x-ui.button>
<x-ui.button href="{{ route('models.edit', $model) }}">Edit</x-ui.button>

{{-- After --}}
<x-ui.button href="{{ route('models.settings.edit') }}">Create</x-ui.button>
<x-ui.button href="{{ route('models.settings.edit', $model) }}">Edit</x-ui.button>
```

### Step 10: Update Tests

Update test route references:

```php
// Before
->get(route('models.create'))
->get(route('models.edit', $model))

// After
->get(route('models.settings.edit'))
->get(route('models.settings.edit', $model))
```

### Step 11: Delete Old Create File

```bash
rm resources/views/pages/models/⚡create.blade.php
```

---

## Complete Example

### PHP Block Structure

```php
<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\YourModel;
use Livewire\Attributes\Locked;

new class extends BasePageComponent {
    #[Locked]
    public bool $isCreateMode = true;
    
    public ?YourModel $model = null;
    
    // Form fields
    public string $name = '';
    public ?string $description = null;
    
    public function mount(?YourModel $model = null): void
    {
        $this->authorizeAccess($model);
        $this->initializeModel($model);
        $this->updatePageHeader();
    }
    
    protected function authorizeAccess(?YourModel $model): void
    {
        $permission = $model 
            ? Permissions::EDIT_MODELS() 
            : Permissions::CREATE_MODELS();
        
        $this->authorize($permission);
    }
    
    protected function initializeModel(?YourModel $model): void
    {
        if ($model) {
            $this->loadExistingModel($model);
            return;
        }
        
        $this->prepareNewModel();
    }
    
    protected function loadExistingModel(YourModel $model): void
    {
        $this->model = $model;
        $this->isCreateMode = false;
        $this->fillFromModel();
    }
    
    protected function prepareNewModel(): void
    {
        $this->model = new YourModel();
    }
    
    protected function updatePageHeader(): void
    {
        if ($this->isCreateMode) {
            $this->pageTitle = __('pages.common.create.title', ['type' => __('types.model')]);
        } else {
            $this->pageTitle = __('pages.common.edit.title', ['type' => __('types.model')]);
        }
    }
    
    protected function fillFromModel(): void
    {
        $this->name = $this->model->name;
        $this->description = $this->model->description;
    }
    
    protected function rules(): array
    {
        $uniqueRule = $this->isCreateMode
            ? Rule::unique(YourModel::class)
            : Rule::unique(YourModel::class)->ignore($this->model->id);
        
        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => ['nullable', 'string'],
        ];
    }
    
    public function create(): void
    {
        $this->validate();
        
        $model = YourModel::create([
            'name' => $this->name,
            'description' => $this->description,
        ]);
        
        $this->sendSuccessNotification($model, 'pages.common.create.success');
        $this->redirect(route('models.show', $model), navigate: true);
    }
    
    public function save(): void
    {
        $this->validate();
        
        $this->model->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);
        
        $this->sendSuccessNotification($this->model, 'pages.common.edit.success');
        $this->redirect(route('models.show', $this->model), navigate: true);
    }
    
    protected function sendSuccessNotification(YourModel $model, string $messageKey): void
    {
        NotificationBuilder::make()
            ->title($messageKey, ['name' => $model->name])
            ->success()
            ->persist()
            ->send();
    }
    
    // Computed properties
    public function getSubmitButtonTextProperty(): string
    {
        return $this->isCreateMode 
            ? __('pages.common.create.submit') 
            : __('pages.common.edit.submit');
    }
    
    public function getSubmitActionProperty(): string
    {
        return $this->isCreateMode ? 'create' : 'save';
    }
    
    public function getCancelUrlProperty(): string
    {
        return $this->isCreateMode
            ? route('models.index')
            : route('models.show', $this->model);
    }
}; ?>
```

---

## Checklist

Use this checklist when applying the pattern:

- [ ] **Step 1**: Make model parameter optional in mount
- [ ] **Step 2**: Add `isCreateMode` locked property
- [ ] **Step 3**: Break down mount into SRP methods
- [ ] **Step 4**: Add create() method
- [ ] **Step 5**: Extract helper methods (prepareData, sendNotification, etc.)
- [ ] **Step 6**: Add computed properties for Blade
- [ ] **Step 7**: Update Blade template to use computed properties
- [ ] **Step 8**: Update routes with optional parameter
- [ ] **Step 9**: Update all navigation links
- [ ] **Step 10**: Update tests
- [ ] **Step 11**: Delete old create file
- [ ] **Step 12**: Run tests to verify
- [ ] **Check**: Do NOT wrap in `<x-layouts.app>` (handled by config)

---

## Benefits

1. **Code Reusability**: Eliminates duplicate code between create/edit
2. **Maintainability**: Single source of truth for forms
3. **Clean Architecture**: Follows SRP and separation of concerns
4. **Testing**: Easier to test one component vs two
5. **Consistency**: Ensures create and edit always stay in sync

---

## Common Pitfalls

### ❌ Forgetting to Update Route Model Binding

**Problem**: Using `{uuid}` instead of `{model}` breaks automatic binding.

```php
// Wrong
Route::livewire('/edit/{uuid}', ...)

// Correct
Route::livewire('/edit/{model?}', ...)
```

### ❌ Inline Logic in Blade

**Problem**: Keeping ternaries and conditionals in templates.

```blade
{{-- Wrong --}}
{{ $isCreateMode ? __('create') : __('edit') }}

{{-- Correct --}}
{{ $this->pageTitle }}
```

### ❌ Forgetting Test Updates

**Problem**: Tests fail because route names changed.

**Solution**: Search codebase for old route references and update them.

### ❌ Not Using Computed Properties

**Problem**: Complex expressions in Blade templates.

**Solution**: Move ALL logic to computed properties in PHP.

---

## Related Patterns

- **Single Responsibility Principle (SRP)**: `docs/AGENTS/development-conventions.md`
- **Clean Blade Templates**: `docs/AGENTS/development-conventions.md`
- **Livewire Route Model Binding**: `docs/AGENTS/livewire-route-model-binding.md`
