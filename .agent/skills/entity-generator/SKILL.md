---
name: entity-generator
description: >
  Full-stack entity scaffolding skill for Laravel + Livewire multi-tenant applications.
  Use this skill whenever the user asks to "create an entity", "scaffold a module", "add a new model",
  "generate a CRUD", or mentions building something like a Plan, Subscription, Project, Invoice, or
  any domain object in a Laravel app. Also trigger when the user says things like "add X to the sidebar",
  "create the migration for Y", or "set up tests for Z entity" — even if they don't say "entity" explicitly.
  This skill covers the full vertical slice: migration, model, enums, translations, seeders, Livewire
  DataTable, Volt SFC edit page, list page, routes, sidebar registration, and Pest feature tests.
---

# Entity Generator

Scaffolds a complete full-stack entity in a multi-tenant Laravel + Livewire application, from the
database layer through to tested UI. Follow the three-phase protocol below in order unless the user
specifies otherwise.

---

## Before You Start

Clarify these if not already stated:

| Question | Why it matters |
| :--- | :--- |
| Entity name (singular/plural)? | Drives all file names and routes. |
| Domain / bounded context? | Determines Enum path and sidebar section. |
| `central` or `tenant` DB connection? | Affects migration and model setup. |
| Any non-standard fields or statuses? | Shapes enums, factory, and translation keys. |

---

## Phase 1 — Database & Logic (Backend)

### 1.1 Model & Migration

```bash
php artisan make:model {Entity} -m
```

- **Extend** `App\Models\Base\BaseModel` (provides UUID primary key support).
- **Migration**: use `$table->uuid('id')->primary()` and proper foreign keys.
- Define `$fillable`, casts, and relationships on the model.

```php
// app/Models/{Entity}.php
class {Entity} extends BaseModel
{
    protected $fillable = [/* ... */];

    protected $casts = [
        'status' => {Entity}Status::class,
    ];
}
```

### 1.2 Enums

Place all enums in `app/Enums/{Domain}/`.

```php
// app/Enums/{Domain}/{Entity}Status.php
enum {Entity}Status: string
{
    case ACTIVE   = 'active';
    case INACTIVE = 'inactive';
}

### 1.3 RBAC Registration (CRITICAL)

All new entities MUST be registered in the RBAC (Role-Based Access Control) system.

1.  Add the entity constant to `App\Constants\Auth\PermissionEntity.php`.
2.  Add the supported actions for the entity in `App\Services\Auth\PermissionMatrix.php`.
3.  Add the corresponding `@method static string ...` annotations to `App\Constants\Auth\Permissions.php`.
4.  Ensure `Trash` is also treated as an entity if the model uses `SoftDeletes`.

This ensures the new entity can be used with `Permissions::ACTION_ENTITY()` calls and appears in the permission matrix UI.

### 1.4 Colorization & Icons Theme (MANDATORY)

All action buttons MUST have an **icon**, a **label**, and follow the standardized **colorization theme**.

#### Architectural Slots (CRITICAL)
Always respect the project's page layout architecture by placing buttons in their designated slots:
-   **Create/Index Actions**: Use `<x-slot:topActions>` in the Index view.
-   **Show Actions**: Use `<x-slot:topActions>` in the Show view.
-   **Edit/Create Form Actions**: Use `<x-slot:bottomActions>` in the Edit/Create view.

#### Action Synchronization (CRITICAL)
The actions available in the **Show view** MUST be synchronized with the **row actions** in the DataTable. 

-   **Completeness**: If an action exists in the DataTable (e.g., Edit, Delete, Archive, Publish), it SHOULD also be available in the Show view's `<x-slot:topActions>`.
-   **Permission Parity**: Action conditions (`@can`) MUST match between the DataTable and the Show view.
-   **Confirmation**: Destructive or status-changing actions (Delete, Archive, Publish) MUST include a confirmation step in both locations.

#### Domain & Settings Visibility (NEW)
Entities that have sub-resources (like Tenants with Domains) or complex settings MUST use a tabbed interface in the **Show** view to organize information clearly.
-   Use DaisyUI `tabs tabs-lifted` for navigation.
-   Include tabs for `Overview`, `Sub-resources` (e.g., `Domains`, `Users`), and `Settings`.

Example synchronization:
-   **DataTable**: `Action::make('delete')->can(Permissions::DELETE_USERS())->confirm(...)`
-   **Show View**: `@can(Permissions::DELETE_USERS()) <x-ui.button ... x-on:click="confirmModal(...)"> ... @endcan`

#### Action Buttons Standard
Use the `->icon()`, `->label()`, and `->color()` methods in DataTables. In Blade views, use `<x-ui.button>` with the `icon` and `color` attributes (preferred) or an embedded `<x-ui.icon>`.

**ALWAYS use `color="primary"` (or info/success/etc) instead of `variant="primary"`.**

-   **View/Show**: `icon('eye')`, `color('info')`
-   **Edit/Update**: `icon('pencil')`, `color('primary')`
-   **Delete**: `icon('trash')`, `color('error')`
-   **Restore**: `icon('arrow-path')`, `color('success')`
-   **Publish/Activate**: `icon('check')`, `color('success')`
-   **Deactivate/Archive**: `icon('archive-box')`, `color('warning')`
-   **Cancel/Back**: `icon('x-mark')`, `color('secondary')` (or ghost)
-   **Save/Create**: `icon('check')`, `color('primary')`
-   **Add New/Create Entity**: `icon('plus')`, `color('primary')` (Standard for `index.blade.php` top actions)
-   **Switch/Navigate**: `icon('arrow-path')` (or `arrow-right-start-on-rectangle`), `color('success')`

#### Row Actions vs Row Click (CRITICAL)
Always prefer explicit **Row Actions** for primary operations (Edit, Delete, Switch) rather than relying solely on `rowClick`.
-   **DataTable `rowClick`**: Should only be used for navigation (e.g., View/Show).
-   **DataTable `rowActions`**: Should contain all functional actions (Edit, Delete, Switch, Archive).
-   **Tenant Entities**: For entities representing organizations or switchable contexts (like Tenants), provide a dedicated "Switch" action with `icon('arrow-path')` and `color('success')` in the `rowActions`.

#### Translatable Relations in DataTables (NEW)
When a column displays a value from a related model (e.g., a Plan's name), and that name is translatable:
-   **Eager Loading**: Always include the relation in the `baseQuery()`'s `with()` array.
-   **Column Formatting**: Use `->format(fn ($value, $row) => $row->relation?->name)` to ensure the current locale is respected via Spatie's `HasTranslations` trait on the related model.

Example in a DataTable:

```php
protected function rowActions(): array
{
    return [
        Action::make('view', __('actions.view'))
            ->icon('eye')
            ->color('info')
            ->route(fn ($model) => route('entities.show', $model->uuid)),

        Action::make('edit', __('actions.edit'))
            ->icon('pencil')
            ->color('primary')
            ->route(fn ($model) => route('entities.edit', $model->uuid)),

        Action::make('delete', __('actions.delete'))
            ->icon('trash')
            ->color('error')
            ->confirm(__('actions.confirm_delete'))
            ->execute(fn ($model) => $model->delete()),
    ];
}
```

### 1.5 Translations

Create both locale files:

- `lang/en_US/{entity}.php`
- `lang/fr_FR/{entity}.php`

Include keys for every field label, page title, and status value:

```php
// lang/en_US/{entity}.php
return [
    'singular'  => '{Entity}',
    'plural'    => '{Entities}',
    'fields'    => [
        'name'   => 'Name',
        'status' => 'Status',
    ],
    'status'    => [
        'active'   => 'Active',
        'inactive' => 'Inactive',
    ],
];
```

### 1.4 Factory & Seeder

```bash
php artisan make:factory {Entity}Factory --model={Entity}
php artisan make:seeder {Entity}Seeder
```

- Factory must produce realistic fake data for every `$fillable` field.
- Seeder populates a sensible initial data set (e.g., default plans, system roles).
- Register the seeder in `DatabaseSeeder`.

---

## Phase 2 — UI & Navigation (Frontend)

### 2.1 DataTable

```php
// app/Livewire/Tables/{Entity}Table.php
class {Entity}Table extends Datatable
{
    // Configure columns: ID, Name, Status, Created At
    // Row actions: Edit → {entities}.edit, Delete (with confirmation)
}
```

- Extend `App\Livewire\DataTable\Datatable`.
- No `@if` directives inside component tags — use `:show` or conditional methods.

### 2.2 Unified Edit SFC (Volt)

```php
// resources/views/pages/{entities}/edit.blade.php
<?php
use function Livewire\Volt\{state, rules, mount};

// Extend BasePageComponent
// Implement mount($id = null), save(), and rules()
// Unified Create/Edit Pattern: mount resolves the model or creates a new instance
?>

<div>
    {{-- Form fields, no @if in component tags --}}
    {{-- All UI text via __('entity.fields.name') etc. --}}
</div>
```

**Unified Create/Edit Pattern rules:**
- `mount` receives optional `$id`; resolves existing model or `new {Entity}()`.
- `save` handles both insert and update in one method.
- `rules` returns a single array covering all fields.
- Colocate any JS in the SFC; no separate script files (CSP compliance).

### 2.3 List Page

```php
// resources/views/pages/{entities}/index.blade.php
// Host <livewire:{entity}-table /> — no logic here
```

### 2.4 Route Registration

Create (or update) `routes/web/auth/{entities}.php`:

```php
Route::prefix('{entities}')->name('{entities}.')->group(function () {
    Route::get('/',          [{Entity}Controller::class, 'index'])  ->name('index');
    Route::get('/create',    [{Entity}Controller::class, 'create']) ->name('create');
    Route::get('/{entity}',  [{Entity}Controller::class, 'edit'])   ->name('edit');
});
```

- Parameter name is **singular** (`{entity}`, not `{entities}`).
- Load the route file from the main web routes bootstrapper.

### 2.5 Sidebar Registration

Open `App\Services\SideBarMenuService` and consult
`docs/AGENTS/sidebar-menu-rules.md` to decide **Platform** vs **Administration** section.

```php
NavigationItem::make()
    ->title(__('navigation.{entities}'))
    ->route('{entities}.index')
    ->activeRoutes('{entities}.*')
    ->show(Auth::user()?->can(Permissions::VIEW_{ENTITIES}()) ?? false),
```

Add the matching permission constant and translation key if they don't exist.

---

## Phase 3 — Quality Control (Testing)

### 3.1 Pest Feature Tests

```php
// tests/Feature/Pages/{Entity}Test.php
```

Cover each of the following cases:

| Test case | Assertion |
| :--- | :--- |
| Index access | Returns 200 for authorised user |
| Create validation | Returns validation errors for missing required fields |
| Store success | Creates record; redirects to index |
| Edit access | Returns 200 and shows existing data |
| Update success | Updates record; redirects |
| Delete | Soft-deletes or hard-deletes; record gone from index |
| **Multi-tenant isolation** | User from Tenant A cannot access Tenant B's records |

### 3.2 Final Verification

```bash
php artisan test --parallel
vendor/bin/pint --dirty --format agent
```

Fix any failures before marking the entity complete.

---

## Standards Checklist

Run through this before declaring done:

- [ ] **UUIDs** — Model extends `BaseModel`; migration uses `uuid` primary key.
- [ ] **No `@if` in component tags** — use `:show` or wrapper conditions.
- [ ] **CSP** — All JS colocated in the Volt SFC; no external script files.
- [ ] **Translations** — Every visible string goes through `__()`.
- [ ] **Tenancy** — Migration and queries use the correct DB connection (`central` vs `tenant`).
- [ ] **RBAC** — Sidebar item gated behind the correct `Permissions::` constant.
- [ ] **Tests pass** — `php artisan test --parallel` green.
- [ ] **Code style** — Pint reports no dirty files.
