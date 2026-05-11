# Workflow: Entity Generation (/skill)

This workflow coordinates the creation of a full-stack entity, from database to UI, respecting the project's multi-tenancy and architectural patterns.

## 🔴 CRITICAL: Orchestration Required

This workflow MUST be executed using the `/orchestrate` protocol.

## Step 1: Research & Scope
- **Agent**: `explorer-agent`
- **Action**: Locate existing similar entities to use as reference. Check `app/Models`, `app/Livewire/Pages`, and `app/Livewire/Tables`.

## Step 2: Detailed Planning
- **Agent**: `project-planner`
- **Action**: Create `docs/PLAN.md` with:
    - Database schema (Migration details).
    - Model properties & Relationships.
    - Enum requirements.
    - Permission names.
    - CRUD Form fields & Validation.
    - DataTable Columns & Filters.
    - Route definitions.

## Step 3: Implementation (Parallel)
After user approval of `PLAN.md`, trigger parallel implementation:

### 3.1 Backend Foundation
- **Agent**: `backend-specialist`
- **Tasks**:
    - Create Migration.
    - Create Model (BaseModel + Tenancy).
    - Create Enums.
    - Create Translations (`en_US`, `fr_FR`).

### 3.2 Frontend & UI
- **Agent**: `frontend-specialist`
- **Tasks**:
    - Create Livewire SFC (Unified Pattern).
    - Create DataTable component.
    - Register Routes.
    - Add to Navigation.

### 3.3 Testing
- **Agent**: `test-engineer`
- **Tasks**:
    - Create Pest Feature Tests for the entity.

## Step 4: Verification
- **Command**: `php artisan test --parallel`
- **Command**: `vendor/bin/pint --dirty`

---

## Example Usage
`/skill create plan crud list and relate it to tenant`
