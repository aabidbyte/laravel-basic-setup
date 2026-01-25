<?php

declare(strict_types=1);

namespace App\Services\Trash;

use App\Constants\Auth\PermissionEntity;
use App\Constants\Auth\Permissions;
use App\Models\ErrorLog;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Central registry for trash-manageable entities.
 *
 * This service defines which entities can be managed through the trash system,
 * along with their permissions, routes, and display configuration.
 *
 * To add a new entity to the trash system:
 * 1. Ensure the model uses SoftDeletes trait
 * 2. Add restore/force_delete permissions to PermissionMatrix
 * 3. Add the entity configuration to getEntities() below
 */
class TrashRegistry
{
    /**
     * Get all trash-manageable entities.
     *
     * @return array<string, array{
     *     model: class-string<Model>,
     *     entity: string,
     *     labelSingular: string,
     *     labelPlural: string,
     *     icon: string,
     *     showRoute: string,
     *     viewPermission: string,
     *     restorePermission: string,
     *     forceDeletePermission: string,
     *     columns: array<string, string>
     * }>
     */
    public function getEntities(): array
    {
        return [
            'users' => [
                'model' => User::class,
                'entity' => PermissionEntity::USERS,
                'labelSingular' => __('types.user'),
                'labelPlural' => __('types.users'),
                'icon' => 'users',
                'showRoute' => 'trash.show',
                'viewPermission' => Permissions::VIEW_USERS(),
                'restorePermission' => Permissions::RESTORE_USERS(),
                'forceDeletePermission' => Permissions::FORCE_DELETE_USERS(),
                'columns' => [
                    'name' => __('table.users.name'),
                    'email' => __('table.users.email'),
                ],
            ],
            'roles' => [
                'model' => Role::class,
                'entity' => PermissionEntity::ROLES,
                'labelSingular' => __('types.role'),
                'labelPlural' => __('types.roles'),
                'icon' => 'shield-check',
                'showRoute' => 'trash.show',
                'viewPermission' => Permissions::VIEW_ROLES(),
                'restorePermission' => Permissions::RESTORE_ROLES(),
                'forceDeletePermission' => Permissions::FORCE_DELETE_ROLES(),
                'columns' => [
                    'name' => __('table.roles.name'),
                ],
            ],
            'teams' => [
                'model' => Team::class,
                'entity' => PermissionEntity::TEAMS,
                'labelSingular' => __('types.team'),
                'labelPlural' => __('types.teams'),
                'icon' => 'user-group',
                'showRoute' => 'trash.show',
                'viewPermission' => Permissions::VIEW_TEAMS(),
                'restorePermission' => Permissions::RESTORE_TEAMS(),
                'forceDeletePermission' => Permissions::FORCE_DELETE_TEAMS(),
                'columns' => [
                    'name' => __('table.teams.name'),
                ],
            ],
            'error-logs' => [
                'model' => ErrorLog::class,
                'entity' => PermissionEntity::ERROR_LOGS,
                'labelSingular' => __('types.error_log'),
                'labelPlural' => __('types.error_logs'),
                'icon' => 'exclamation-triangle',
                'showRoute' => 'trash.show',
                'viewPermission' => Permissions::VIEW_ERROR_LOGS(),
                'restorePermission' => Permissions::RESTORE_ERROR_LOGS(),
                'forceDeletePermission' => Permissions::FORCE_DELETE_ERROR_LOGS(),
                'columns' => [
                    'type' => __('table.error_logs.type'),
                    'message' => __('table.error_logs.message'),
                ],
            ],
        ];
    }

    /**
     * Get configuration for a specific entity type.
     *
     * @return array<string, mixed>|null
     */
    public function getEntity(string $entityType): ?array
    {
        return $this->getEntities()[$entityType] ?? null;
    }

    /**
     * Get the model class for an entity type.
     *
     * @return class-string<Model>|null
     */
    public function getModelClass(string $entityType): ?string
    {
        return $this->getEntity($entityType)['model'] ?? null;
    }

    /**
     * Get all entity types that the current user can access.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAccessibleEntities(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        return array_filter(
            $this->getEntities(),
            fn (array $config) => $user->can($config['viewPermission']),
        );
    }

    /**
     * Get all entity type keys (slugs).
     *
     * @return array<string>
     */
    public function getEntityTypes(): array
    {
        return array_keys($this->getEntities());
    }

    /**
     * Check if an entity type exists in the registry.
     */
    public function hasEntity(string $entityType): bool
    {
        return isset($this->getEntities()[$entityType]);
    }

    /**
     * Get the regex pattern for route constraints.
     */
    public function getRoutePattern(): string
    {
        return implode('|', $this->getEntityTypes());
    }
}
