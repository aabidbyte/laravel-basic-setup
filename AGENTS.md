# Agent Documentation

> **Note**: This file has been moved to `docs/AGENTS/index.md` for better organization and maintainability.

The full documentation is now organized into sections for easier navigation and faster indexing. Each major section is in its own file under `docs/AGENTS/`.

## ⚠️ Critical Rules (Always Check First)

### CSP-Safe Alpine.js (MANDATORY)
**All Alpine.js components with methods/functions MUST be extracted to registered components.**

```blade
{{-- ❌ FORBIDDEN --}}
<div x-data="{ toggle() { this.open = !this.open } }">

{{-- ✅ REQUIRED --}}
<div x-data="myComponent()">  {{-- Registered in JS file --}}
```

See [CSP Safety Guide](docs/AGENTS/csp-safety.md) and [Important Patterns](docs/AGENTS/important-patterns.md#csp-safe-alpinejs-development-critical) for full CSP documentation.

## Quick Links

- **[Full Documentation](docs/AGENTS/index.md)** - Complete agent documentation with table of contents
- **[Project Overview](docs/AGENTS/project-overview.md)**
- **[Development Conventions](docs/AGENTS/development-conventions.md)**
- **[Common Tasks](docs/AGENTS/common-tasks.md)**
- **[Important Patterns](docs/AGENTS/important-patterns.md)**

For the complete documentation, please see [docs/AGENTS/index.md](docs/AGENTS/index.md).
