# GEMINI.md - Context & Operational Directives

> **Rule Zero:** Rules guide my behavior. Documentation is the source of truth. I never assume; I always verify.

## 🛸 CORE OPERATIONAL DIRECTIVES

1.  **Dual-Stage Context Acquisition**: I must scan global rules (`AGENTS.md`, `.gemini/GEMINI.md`) then task-specific docs (`docs/**`) before any implementation.
2.  **Convention Match**: Every line of code I write must align with the architecture and logic found in project documentation.
3.  **🔴 Zero-Error Guarantee**: I am responsible for ensuring no syntax errors, broken references, or logical bugs are left unattended.
4.  **🧪 Regression Testing**: I must always execute the test suite (Pest) after modifications.
5.  **The "Never Repeat" Rule**: I will update documentation immediately if a mistake is corrected or a new pattern is established.

## 1. Project Overview

**Name:** Laravel Basic Setup
**Description:** A comprehensive Laravel 12 starter kit featuring Livewire 4, UUID-based models, Fortify authentication, advanced monitoring (Telescope, Horizon, Log Viewer), and real-time capabilities via Reverb.
**Architecture:** Monolithic Laravel application with server-side rendering via Livewire.

## 2. Technology Stack

*   **Backend:** PHP 8.4+, Laravel 12.0
*   **Frontend:** Livewire 4.0 (SFC), Tailwind CSS 4.x, DaisyUI 5.x, Alpine.js (CSP-safe)
*   **Database:** Supports MySQL/PostgreSQL/SQLite (uses UUIDs for primary keys)
*   **Real-time:** Laravel Reverb (WebSocket)
*   **Authentication:** Laravel Fortify (Backend), Sanctum (API), Spatie Permission (RBAC)
*   **Dev Tools:** Vite 7.x, Pest (Testing), Pint (Formatting), Laravel Boost

## 3. Operational Directives (CRITICAL)

### Code & Style
*   **UUIDs:** All models **must** extend `App\Models\Base\BaseModel` or `App\Models\Base\BaseUserModel` to automatically handle UUID generation.
*   **Enums:** Use **backed Enums** instead of class constants for status/types. Located in `app/Enums/`.
*   **Translations:** **Mandatory** for all user-facing text. Add keys to both `en_US` and `fr_FR` in `lang/`.
*   **Imports:** No leading slashes in `use` statements (e.g., `use App\Models\User;` not `use \App\Models\User;`).
*   **Global Namespace for Built-in Functions:** Prefix all PHP built-in functions with `\` when inside a namespaced file (e.g., `\count()`, `\is_array()`).

### Frontend (Blade/Livewire/Alpine)
*   **CSP Safety:** All Alpine.js logic must be extracted to registered components. **Avoid inline event handlers** that violate CSP.
*   **Livewire Routing:** Use the **singular model name** for route parameters to enable automatic binding (e.g., `/users/{user}`), never `{id}` or `{uuid}`.
*   **Blade Components:** Do **not** use Blade directives (like `@if`) inside component opening tags. Use conditional attribute binding instead (`:attr="$condition ? 'val' : null"`).
*   **Layouts:** Full-page Livewire components are automatically wrapped in the layout. **Do not** manually wrap them in `<x-layouts.app>`.

### Testing
*   **Framework:** Pest (v4)
*   **Location:** `tests/Feature` and `tests/Unit`
*   **Command:** `php artisan test` or `composer run test`

## 4. Build & Run Commands

### Setup
```bash
composer run setup
# OR manual:
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### Development
```bash
# Runs Server, Horizon, Pail, Vite, and Reverb concurrently
composer run dev
```

### Testing & Quality
```bash
# Run Tests
php artisan test

# Format Code (PHP)
composer run pint

# Format Code (Blade/JS)
npm run format:all
```

## 5. File Structure Highlights
*   `app/Models/Base/`: Base classes for models (UUID logic).
*   `app/Livewire/`: Livewire components (Standard & SFC).
*   `app/Actions/Fortify/`: Authentication logic.
*   `docs/AGENTS/`: Detailed agent-specific documentation.
*   `lang/`: Localization files (Strictly enforced).

## 6. Agent Behavior
*   **Reference:** Always consult `docs/AGENTS/` for specific patterns before implementation.
*   **Safety:** Do not assume "standard" Laravel conventions if a specific base class or trait is provided (e.g., `BaseModel`).
*   **Verification:** Always run `php artisan test` after changes.
