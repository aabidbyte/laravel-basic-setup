## Summary

### Key Rules

1. ✅ **Always use constants** - Never hardcode role/permission names
2. ✅ **Check permissions, not roles** - Use `can()` and `@can` with permissions
3. ✅ **Users → Roles → Permissions** - Follow the hierarchy
4. ✅ **Use Model Policies** - For access control logic
5. ✅ **Flush cache** - When seeding or using direct DB operations
6. ✅ **Use Gate::before()** - For Super-Admin functionality
7. ✅ **Unset relations** - When switching teams

### Constants Classes

-   `App\Constants\Permissions` - All permission constants
-   `App\Constants\Roles` - All role constants

### Important Files

-   `config/permission.php` - Package configuration
-   `app/Http/Middleware/Teams/TeamsPermission.php` - Teams middleware
-   `app/Providers/AppServiceProvider.php` - Middleware priority
-   `docs/spatie-permission/index.md` - This documentation file

---

**For more details, see**: [Spatie Permission Documentation](https://spatie.be/docs/laravel-permission)
