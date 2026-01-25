# Applying Unified Create/Edit Pattern to Other Domains

> **Purpose**: Practical guide for applying the unified create/edit refactoring pattern to other CRUD resources in this application.

## Quick Start

For each domain you want to refactor:

1. Read [unified-create-edit-pattern.md](unified-create-edit-pattern.md)
2. Follow the checklist in that document
3. Reference domain-specific notes below
4. Run tests after changes

---

## Candidate Domains

### High Priority (Simple CRUD)

These domains are ideal candidates with straightforward create/edit workflows:

#### 1. **Users** (`app/Livewire/Users/`)
- **Current**: Likely has separate create and edit pages
- **Benefit**: High - used frequently, lots of duplication
- **Complexity**: Medium (profile fields, role assignment, password handling)
- **Files**: 
  - Merge `create.blade.php` → `edit.blade.php`
  - Routes: `/users/settings/{user?}`
- **Special considerations**:
  - Password field only required on create
  - Email uniqueness validation
  - Role/permission assignment UI
  - Avatar upload handling

#### 2. **Roles** (`app/Livewire/Roles/`)
- **Current**: Separate create/edit if exists
- **Benefit**: Medium - admin-only, less frequent
- **Complexity**: Low (name, description, permissions)
- **Files**:
  - Merge into unified component
  - Routes: `/roles/settings/{role?}`
- **Special considerations**:
  - Permission assignment checkboxes
  - Guard name handling
  - Super Admin role protection

#### 3. **Teams** (`app/Livewire/Teams/`)
- **Current**: Separate create/edit likely
- **Benefit**: Medium - depends on team usage
- **Complexity**: Low (name, description, members)
- **Files**:
  - Merge into unified component
  - Routes: `/teams/settings/{team?}`
- **Special considerations**:
  - Team member management
  - Owner assignment
  - Team context switching

---

## Domain-Specific Patterns

### Users Domain Example

```php
public function mount(?User $user = null): void
{
    $this->authorizeAccess($user);
    $this->initializeModel($user);
    $this->updatePageHeader();
}

protected function prepareNewModel(): void
{
    $this->model = new User();
    // Default role from query param or config
    $this->roleIds = [request()->query('role_id')] ?? [];
}

protected function rules(): array
{
    $rules = [
        'name' => ['required', 'string', 'max:255'],
        'email' => [
            'required', 
            'email', 
            $this->isCreateMode 
                ? Rule::unique(User::class) 
                : Rule::unique(User::class)->ignore($this->model->id)
        ],
        'roleIds' => ['array'],
    ];

    // Password only required on create
    if ($this->isCreateMode) {
        $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
    }

    return $rules;
}
```

### Roles Domain Example

```php
protected function prepareData(): array
{
    return [
        'name' => $this->name,
        'description' => $this->description,
        'guard_name' => 'web', // Always web guard
    ];
}

public function save(): void
{
    // Protect Super Admin from editing
    if (!$this->isCreateMode && $this->model->name === Roles::SUPER_ADMIN) {
        throw new \Exception('Cannot edit Super Admin role');
    }

    parent::save();
    
    // Sync permissions
    $this->model->syncPermissions($this->permissionIds);
}
```

---

## Migration Strategy

### Phase 1: Low-Risk Domains (Week 1)
1. **Teams** - Low usage, simple structure
2. **Roles** - Admin-only, well-tested

### Phase 2: Medium-Risk Domains (Week 2)
3. **Users** - High usage, needs thorough testing

### Phase 3: Complex Domains (As Needed)
4. Custom domains specific to your app

---

## Testing Checklist per Domain

For each domain you refactor:

- [ ] Create new record via unified page
- [ ] Edit existing record via unified page
- [ ] Verify validation works (create mode)
- [ ] Verify validation works (edit mode)
- [ ] Test cancel button redirects correctly
- [ ] Verify permissions enforced correctly
- [ ] Run automated test suite: `php artisan test --filter={Domain}Test`
- [ ] Manually test in browser
- [ ] Check all navigation links updated

---

## Route Naming Convention

Standardize route names across all domains:

```php
// Settings/Form page (Create + Edit)
Route::livewire('/settings/{model?}', 'pages::domain.edit-settings')
    ->name('domain.settings.edit');

// Builder/Advanced page if needed (Create + Edit)
Route::livewire('/builder/{model?}', 'pages::domain.edit-builder')
    ->name('domain.builder.edit');

// Show page
Route::livewire('/{model}', 'pages::domain.show')
    ->name('domain.show');

// Index
Route::view('/', 'pages.domain.index')
    ->name('domain.index');
```

---

## Common Gotchas per Domain

### Users
- **Password confirmation**: Only show on create
- **Email verification**: Handle state properly
- **Avatar upload**: Ensure works in both modes
- **Current user editing self**: Special permissions

### Roles
- **Super Admin protection**: Cannot edit/delete
- **Permission sync**: Use `syncPermissions()` not manual
- **Guard name**: Always set to 'web'

### Teams
- **Team ownership**: Transfer owner carefully
- **Team switching**: Update session correctly
- **Member management**: Handle invitations properly

---

## Verification Script

Run this after each domain refactor:

```bash
#!/bin/bash

DOMAIN=$1  # e.g., "users", "roles", "teams"

echo "Testing ${DOMAIN} domain refactoring..."

# Check routes exist
php artisan route:list | grep "${DOMAIN}.settings.edit" || echo "❌ Route missing"

# Run tests
php artisan test --filter="${DOMAIN^^}Test" || echo "❌ Tests failed"

# Check for old route references
grep -r "${DOMAIN}.create" app/ resources/ tests/ && echo "⚠️ Old create route found"
grep -r "${DOMAIN}.edit\"" app/ resources/ tests/ | grep -v "settings.edit" && echo "⚠️ Old edit route found"

echo "✅ ${DOMAIN} verification complete"
```

---

## Rollback Plan

If a refactoring causes issues:

1. **Revert files**:
   ```bash
   git checkout HEAD -- resources/views/pages/{domain}/
   git checkout HEAD -- routes/web/auth/{domain}.php
   ```

2. **Restore old create file** from git history

3. **Update route references** back to old routes

4. **Run tests** to verify rollback successful

---

## Success Metrics

Track these for each domain:

- ✅ Lines of code reduced (expect 30-40% reduction)
- ✅ Test coverage maintained or improved
- ✅ No regressions in existing functionality
- ✅ Improved maintainability (subjective)
- ✅ Consistent UX across create/edit

---

## Next Steps

1. **Choose a domain** from the candidate list
2. **Read the main pattern doc**: [unified-create-edit-pattern.md](unified-create-edit-pattern.md)
3. **Create a branch**: `git checkout -b refactor/{domain}-create-edit`
4. **Follow the checklist** step-by-step
5. **Test thoroughly**
6. **Commit and review**
7. **Repeat for next domain**

---

## Questions?

Refer back to:
- [Unified Create/Edit Pattern](unified-create-edit-pattern.md) - Complete pattern guide
- [Development Conventions](development-conventions.md) - SRP and Clean Blade rules
- [Email Templates Walkthrough](file:///Users/hop/.gemini/antigravity/brain/5a88e313-34c8-4119-b8c0-5fd7c6916c1c/walkthrough.md) - Real-world example
