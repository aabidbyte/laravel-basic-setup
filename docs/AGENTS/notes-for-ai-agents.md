## Notes for AI Agents

1. **Always check existing code** before creating new components
2. **Use Laravel Boost tools** for documentation and debugging
3. **Follow existing patterns** - check sibling files for conventions
4. **Test all changes** - write or update tests
5. **Format code** with Pint before finalizing
6. **Use stable configs** - prefer `config()` over `env()` where possible
7. **Base model classes required** - ALL new models must extend `App\Models\Base\BaseModel` or `App\Models\Base\BaseUserModel`
8. **UUID columns required** - ALL tables must have a UUID column in their migrations
9. **UUID generation required** - ALL models automatically generate UUIDs via base classes
10. **Fix Intelephense errors** - Always update `IntelephenseHelper.php` when encountering undefined method errors
11. **PSR-4 compliance required** - ALL classes must follow PSR-4 autoloading standards. Test support classes must be in `tests/Support/` with proper namespaces, never defined directly in test files
12. **Avoid hardcoding URLs in navigation**: Always use route names via `->route()` or dynamic configuration for external tools.
13. **Follow sidebar menu organization rules**: Separate business features from administration and use collapsible groups in the bottom section (see `sidebar-menu-rules.md`).
14. **Use constants, avoid duplication** - Always use constants instead of hardcoded strings when possible, and always avoid duplication for easy maintenance
15. **Use Reusable Components** - Always use `x-ui.*` components for buttons, inputs, checkboxes, toggles, selects, etc. instead of raw HTML. create them if they don't exist in `resources/views/components/ui/` and document them.
16. **Component documentation required** - **ALWAYS update `docs/components/index.md` when adding new UI components** - Include props, usage examples, implementation details, and add to component index
17. **Use Page Layout for CRUD views** - All CRUD views (index, create, edit, show) MUST use `x-layouts.page` component for consistent structure with back button and action slots. See `docs/components/page-layout.md`
18. **Responsive design required** - ALL layouts and views MUST be responsive, supporting mobile, tablet, and desktop devices using Tailwind's responsive prefixes (sm:, md:, lg:, xl:). Use mobile-first approach.
19. **Update this file** when adding new patterns, conventions, or features
20. **Route organization required** - All routes must follow the domain-based structure in `routes/web/` with proper separation: `public/` (no auth), `auth/` (requires login), `dev/` (local only). See `docs/AGENTS/routes.md` for full conventions.
