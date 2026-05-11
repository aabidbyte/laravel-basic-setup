# SKILL: Entity Generator (A-Z)

> **Purpose**: This skill provides a standardized, high-quality protocol for generating complete entities from scratch, including database migrations, models, enums, Livewire CRUD pages, DataTables, routes, and tests.

## 🚀 Activation Protocol

Activate this skill when the user asks to "create an entity", "build a CRUD", or "generate a full module".

**MANDATORY:** Before implementation, you MUST use the `/orchestrate` workflow to involve at least 3 agents:
1. `backend-specialist`: Models, Migrations, Enums, Logic.
2. `frontend-specialist`: Livewire SFC, DataTables, UI.
3. `test-engineer`: Pest Tests.

---

## 🏗️ The A-Z Generation Blueprint

### Phase 1: Foundation (Backend)

#### 1. Migration
- **Location**: `database/migrations/tenant/` if tenant-related, otherwise `database/migrations/`.
- **Naming**: `202X_XX_XX_XXXXXX_create_entities_table.php`.
- **Rule**: Use UUIDs for IDs. Add `tenant_id` if using Single Database Tenancy (check `config/tenancy.php`).

#### 2. Model
- **Location**: `app/Models/`.
- **Base Class**: Must extend `App\Models\Base\BaseModel` for UUID support.
- **Tenancy**: If tenant-scoped, use `Stancl\Tenancy\Database\Concerns\BelongsToTenant`.

#### 3. Enums
- **Location**: `app/Enums/[Entity]/`.
- **Rules**: Use Backed Enums (string). Include `color()` and `label()` methods.
- **Registration**: Add to `config/translation-resolvers.php`.

#### 4. Translations
- **Location**: `lang/en_US/` and `lang/fr_FR/`.
- **Keys**: Create a new file for the entity (e.g., `entities.php`).

---

### Phase 2: User Interface (Frontend)

#### 1. Livewire SFC (Unified CRUD)
- **Location**: `app/Livewire/Pages/[Entity]/EditEntity.php` (for both create and edit).
- **Pattern**: Follow `docs/AGENTS/unified-create-edit-pattern.md`.
- **Base Class**: Extend `App\Livewire\Bases\BasePageComponent`.
- **Logic**: Use computed properties for titles and actions.

#### 2. DataTable (List)
- **Location**: `app/Livewire/Tables/[Entity]Table.php`.
- **Base Class**: Extend `App\Services\DataTable\Builders\Table`.
- **View**: Create a lightweight Blade view if necessary, or use anonymous component.

---

### Phase 3: Integration

#### 1. Routes
- **Location**: `routes/tenant.php` (if in tenant context) or `routes/web.php`.
- **Pattern**: Use `Route::livewire()`. Follow singular model naming for binding.

#### 2. Sidebar/Navigation
- **Location**: `config/navigation.php` or `app/Services/Navigation/`.
- **Rule**: Respect `docs/AGENTS/sidebar-menu-rules.md`.

---

### Phase 4: Quality Assurance

#### 1. Pest Tests
- **Location**: `tests/Feature/Pages/[Entity]Test.php`.
- **Pattern**: Use `it()` syntax. Test create, edit, list, and authorization.
- **Workflow**: Activate `pest-testing` skill.

#### 2. Verification
- **Command**: `php artisan test --parallel`.
- **Linting**: `vendor/bin/pint --dirty`.

---

## 📝 Implementation Template (Implementation Plan)

When creating the `PLAN.md` via `project-planner`, ensure it covers:

1. **Schema Design**: Columns, Types, Relationships.
2. **Authorization**: Permission names (e.g., `entities.view`, `entities.create`).
3. **UI/UX**: Form fields, Table columns, Filter options.
4. **Tenancy Context**: Is it a Central or Tenant entity?

---

## 🔴 CRITICAL RULES

1. **NO DIRECTIVES IN TAGS**: Never use `@if` inside component tags.
2. **CSP SAFETY**: Extract Alpine logic to components.
3. **PARAMETER LIMIT**: DTOs for methods with >3 params.
4. **MANDATORY TRANSLATIONS**: Never leave hardcoded strings.
