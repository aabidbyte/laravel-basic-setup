<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Constants\Auth\PermissionAction;
use App\Constants\Auth\PermissionEntity;

/**
 * Permission Matrix Service.
 *
 * Centralized service for managing the permission matrix that maps
 * entities to their supported actions. This is the single source of truth
 * for which permissions exist in the system.
 *
 * The matrix approach allows for:
 * - Clear visualization of all permissions
 * - Easy addition of new entities or actions
 * - Centralized management of permission structure
 * - Future extensibility for database-driven entities
 */
class PermissionMatrix
{
    /**
     * Get the complete permission matrix.
     * Returns an array where keys are entity names and values are arrays of supported actions.
     *
     * @return array<string, array<string>>
     */
    public function getMatrix(): array
    {
        return [
            PermissionEntity::USERS => [
                PermissionAction::VIEW,
                PermissionAction::CREATE,
                PermissionAction::EDIT,
                PermissionAction::DELETE,
                PermissionAction::ACTIVATE,
                PermissionAction::EXPORT,
                PermissionAction::GENERATE_ACTIVATION,
            ],
            PermissionEntity::ROLES => [
                PermissionAction::VIEW,
                PermissionAction::CREATE,
                PermissionAction::EDIT,
                PermissionAction::DELETE,
            ],
            PermissionEntity::TEAMS => [
                PermissionAction::VIEW,
                PermissionAction::CREATE,
                PermissionAction::EDIT,
                PermissionAction::DELETE,
            ],
            PermissionEntity::DOCUMENTS => [
                PermissionAction::VIEW,
                PermissionAction::CREATE,
                PermissionAction::EDIT,
                PermissionAction::DELETE,
                PermissionAction::PUBLISH,
                PermissionAction::UNPUBLISH,
            ],
            PermissionEntity::ARTICLES => [
                PermissionAction::VIEW,
                PermissionAction::CREATE,
                PermissionAction::EDIT,
                PermissionAction::DELETE,
                PermissionAction::PUBLISH,
                PermissionAction::UNPUBLISH,
                PermissionAction::EXPORT,
            ],
            PermissionEntity::POSTS => [
                PermissionAction::VIEW,
                PermissionAction::CREATE,
                PermissionAction::EDIT,
                PermissionAction::DELETE,
                PermissionAction::RESTORE,
                PermissionAction::EXPORT,
            ],
            PermissionEntity::ERROR_LOGS => [
                PermissionAction::VIEW,
                PermissionAction::RESOLVE,
                PermissionAction::DELETE,
                PermissionAction::EXPORT,
            ],
            PermissionEntity::TELESCOPE => [
                PermissionAction::ACCESS,
            ],
            PermissionEntity::HORIZON => [
                PermissionAction::ACCESS,
            ],
            PermissionEntity::MAIL_SETTINGS => [
                PermissionAction::VIEW,
                PermissionAction::CONFIGURE,
            ],
        ];
    }

    /**
     * Get all entities in the matrix.
     *
     * @return array<string>
     */
    public function getEntities(): array
    {
        return array_keys($this->getMatrix());
    }

    /**
     * Get all unique actions across all entities.
     *
     * @return array<string>
     */
    public function getAllActions(): array
    {
        $actions = [];
        foreach ($this->getMatrix() as $entityActions) {
            $actions = array_merge($actions, $entityActions);
        }

        return array_values(array_unique($actions));
    }

    /**
     * Get actions supported by a specific entity.
     *
     * @param  string  $entity  Entity constant
     * @return array<string>
     */
    public function getActionsForEntity(string $entity): array
    {
        return $this->getMatrix()[$entity] ?? [];
    }

    /**
     * Check if an entity supports a specific action.
     *
     * @param  string  $entity  Entity constant
     * @param  string  $action  Action constant
     */
    public function entitySupportsAction(string $entity, string $action): bool
    {
        return in_array($action, $this->getActionsForEntity($entity), true);
    }

    /**
     * Generate the permission name for an entity-action pair.
     * Format: "{action} {entity}" (e.g., "view users", "edit roles")
     *
     * @param  string  $entity  Entity constant
     * @param  string  $action  Action constant
     */
    public function getPermissionName(string $entity, string $action): string
    {
        return "{$action} {$entity}";
    }

    /**
     * Get all permission names as a flat array.
     *
     * @return array<string>
     */
    public function getAllPermissionNames(): array
    {
        $permissions = [];
        foreach ($this->getMatrix() as $entity => $actions) {
            foreach ($actions as $action) {
                $permissions[] = $this->getPermissionName($entity, $action);
            }
        }

        return $permissions;
    }

    /**
     * Get permissions grouped by entity.
     * Returns an array where keys are entity names and values are arrays of permission names.
     *
     * @return array<string, array<string>>
     */
    public function getPermissionsByEntity(): array
    {
        $grouped = [];
        foreach ($this->getMatrix() as $entity => $actions) {
            $grouped[$entity] = [];
            foreach ($actions as $action) {
                $grouped[$entity][] = $this->getPermissionName($entity, $action);
            }
        }

        return $grouped;
    }

    /**
     * Get all entities that support a specific action.
     *
     * @param  string  $action  Action constant
     * @return array<string>
     */
    public function getEntitiesForAction(string $action): array
    {
        $entities = [];
        foreach ($this->getMatrix() as $entity => $actions) {
            if (in_array($action, $actions, true)) {
                $entities[] = $entity;
            }
        }

        return $entities;
    }

    /**
     * Parse a permission name into entity and action.
     *
     * @param  string  $permissionName  Permission name (e.g., "view users")
     * @return array{entity: string, action: string}|null
     */
    public function parsePermissionName(string $permissionName): ?array
    {
        $parts = explode(' ', $permissionName, 2);
        if (count($parts) !== 2) {
            return null;
        }

        return [
            'action' => $parts[0],
            'entity' => $parts[1],
        ];
    }

    /**
     * Get the matrix as a format suitable for UI rendering.
     * Returns entities as rows with their actions as checkable columns.
     *
     * @return array<int, array{entity: string, label: string, actions: array<string, bool>}>
     */
    public function getMatrixForUI(): array
    {
        $allActions = $this->getAllActions();
        $result = [];

        foreach ($this->getMatrix() as $entity => $supportedActions) {
            $actionsMap = [];
            foreach ($allActions as $action) {
                $actionsMap[$action] = in_array($action, $supportedActions, true);
            }

            $result[] = [
                'entity' => $entity,
                'label' => PermissionEntity::getLabel($entity),
                'actions' => $actionsMap,
            ];
        }

        return $result;
    }
}
