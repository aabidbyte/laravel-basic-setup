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
```

### 1.3 Translations

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

### 1.5 RBAC & Permissions
1. Add entity constant to `App\Constants\Auth\PermissionEntity`.
2. Ensure `App\Services\Auth\PermissionMatrix` supports `VIEW`, `CREATE`, `EDIT`, `DELETE`, `RESTORE`, and `FORCE_DELETE`.
3. Add `@method static string ...` docblocks to `App\Constants\Auth\Permissions`.

### 1.6 Trash Registry
Register the entity in `App\Services\Trash\TrashRegistry` to enable the global trash UI.


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

### 2.2 Unified Edit SFC (Livewire 4 sfc)

```php
// resources/views/pages/{entities}/edit.blade.php
<?php
use function Livewire\{state, rules, mount};

// Extend BasePageComponent
// Implement mount($id = null), save(), and rules()
// Unified Create/Edit Pattern: mount resolves the model or creates a new instance
?>

<div>
    {{-- Form fields, no @if in component tags --}}
    {{-- All UI text via __('entity.fields.name') etc. --}}
</div>
```

### 2.1 The Unified Edit Page (Livewire 4 SFC)
**Path**: `resources/views/pages/{entities}/edit.blade.php`

Follow these strict rules from `docs/AGENTS/unified-create-edit-pattern.md`:
1. **Optional Parameter**: `mount(?{Entity} $entity = null)` - always use model name (singular).
2. **Locked Mode**: `#[Locked] public bool $isCreateMode = true;`
3. **SRP mount()**: Use `initializeUnifiedModel` helper from `BasePageComponent`.
4. **Computed Properties**: ALL UI logic (titles, buttons, actions, URLs) MUST be in `#[Computed]` methods.
5. **No Blade Logic**: Templates should be clean; no `@if` for mode switching—use `$this->pageTitle`, `$this->submitAction`, etc.
6. **UUID Only**: When communicating with the backend (e.g., in sublists or redirects), always use `$model->uuid`.

### 2.2 Sublist DataTables
If the entity has children (e.g., Plans have Subscriptions, Tenants have Users), **NEVER** use simple `@foreach` lists.
1. Create a specialized DataTable in `app/Livewire/Tables/`.
2. Embed it in the parent `show.blade.php` or `edit.blade.php`.
3. Use `lazy` loading for sublist tables.

### 2.3 The Main DataTable
**Path**: `app/Livewire/Tables/{Entity}Table.php`
1. **Trash Support**: Add a "View Trash" button in `topActions()` linking to `route('trash.index', ['entityType' => '{entities}'])`.
2. **Row Actions**: 
   - `edit` links to `route('{entities}.edit', $model->uuid)`.
   - `delete` performs soft-delete.
3. **Bulk Actions**: Include `activate`, `deactivate`, and `delete`.

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
- [ ] **CSP** — All JS colocated in the livewire 4 SFC; no external script files.
- [ ] **Translations** — Every visible string goes through `__()`.
- [ ] **Tenancy** — Migration and queries use the correct DB connection (`central` vs `tenant`).
- [ ] **RBAC** — Sidebar item gated behind the correct `Permissions::` constant.
- [ ] **Tests pass** — `php artisan test --parallel` green.
- [ ] **Code style** — Pint reports no dirty files.
- [ ] Model extends `BaseModel` + Migration has `softDeletes()`.
- [ ] Entity registered in `TrashRegistry`.
- [ ] Edit page uses `initializeUnifiedModel` and `#[Computed]` properties.
- [ ] No `@if` in component opening tags.
- [ ] Related entities use DataTables, not loops.
- [ ] Navigation and tests use `uuid` exclusively.