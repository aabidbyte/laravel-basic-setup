# Tenant Feature Entitlements

## Goal
Add an admin-managed central entitlement system so plans define default features and individual tenants can receive custom feature overrides without storing billing rules in tenant databases.

## Tasks
- [ ] Inspect existing plan/subscription permissions, seeders, factories, and tests → Verify: affected files and current conventions are identified before editing.
- [ ] Add central enums and models for `Feature`, `PlanFeature`, and `TenantFeatureOverride` → Verify: models use central connection, typed casts, relationships, and project naming conventions.
- [ ] Add central migration tables and indexes for feature catalog, plan defaults, and tenant overrides → Verify: foreign keys target central `plans` and `tenants`, no tenant migration is added.
- [ ] Add factories and seed catalog/default plan features from current `plans.features` JSON values → Verify: seeded Basic/Pro/Enterprise/Lifetime plans resolve the same feature keys as today.
- [ ] Add `FeatureResolver` service for override-first resolution → Verify: active tenant override wins, expired override is ignored, active subscription plan default is used otherwise.
- [ ] Add focused Pest tests for resolver behavior and tenant isolation → Verify: scoped `php artisan test --compact --filter=FeatureResolver` passes without `RefreshDatabase`.
- [ ] Run formatting and relevant test lane → Verify: `vendor/bin/pint --dirty --format agent` and the focused tests pass.

## Done When
- [ ] Admin-managed feature entitlements are represented centrally.
- [ ] Existing plan feature JSON no longer needs to be read by new entitlement checks.
- [ ] Tests prove plan defaults, tenant overrides, disabled overrides, expiry windows, and tenant isolation.

## Notes
- Scope is backend/domain first; no customer-facing billing or add-on purchase flow.
- Keep `subscriptions.extras` for notes/backward compatibility, not as the entitlement source of truth.
- Do not modify unrelated dirty files already present in the worktree.
