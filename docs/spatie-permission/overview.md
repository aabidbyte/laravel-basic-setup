## Overview

This application uses **Spatie Permission v6.23** for role and permission management. The package is configured to work with UUID-based User models and includes teams permissions support.

### Key Features

-   **UUID Support**: Configured to use `model_uuid` instead of `model_id` for UUID-based User models
-   **Teams Permissions**: Enabled by default
-   **User Model**: `App\Models\User` includes the `HasRoles` trait
-   **Configuration**: `config/permission.php`
-   **Migration**: Modified to support UUIDs in pivot tables

### Important Constraints

⚠️ **CRITICAL**: The User model must NOT have:

-   `role` or `roles` property/relation/method
-   `permission` or `permissions` property/relation/method

These will interfere with the package's functionality.

