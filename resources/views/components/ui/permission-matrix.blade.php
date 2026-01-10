{{--
    Permission Matrix Component

    A table-based UI component for displaying and editing permissions in a matrix format.
    Entities are displayed as rows, and actions as columns with checkboxes at intersections.

    Props:
    - permissions: Collection of all Permission models
    - selectedPermissions: array of selected permission IDs (for edit mode)
    - wireModel: optional wire:model binding name for Livewire integration
    - readonly: boolean for view-only mode (false by default)
--}}
@props([
    'permissions' => collect(),
    'selectedPermissions' => [],
    'wireModel' => null,
    'readonly' => false,
])

@php
    use App\Services\Auth\PermissionMatrix;
    use App\Constants\Auth\PermissionAction;
    use App\Constants\Auth\PermissionEntity;
    use App\Constants\Auth\Roles;

    $matrix = new PermissionMatrix();
    $matrixData = $matrix->getMatrix();
    $allActions = $matrix->getAllActions();
    $superAdminOnlyEntities = $matrix->getSuperAdminOnlyEntities();

    // Filter out super_admin-only entities if current user is not super_admin
    $isSuperAdmin = auth()->check() && auth()->user()->hasRole(Roles::SUPER_ADMIN);
    if (!$isSuperAdmin) {
        $matrixData = array_filter(
            $matrixData,
            fn($entity) => !in_array($entity, $superAdminOnlyEntities, true),
            ARRAY_FILTER_USE_KEY,
        );
    }

    // Create a lookup map: permission name => permission model
    $permissionLookup = $permissions->keyBy('name');
@endphp

<div class="permission-matrix-container overflow-x-auto">
    <table class="table-zebra table-compact table w-full">
        <thead>
            <tr>
                <th class="bg-base-200 sticky left-0 z-10 min-w-48">
                    {{ __('permissions.entities.users') ? __('permissions.matrix.title') : 'Permission Matrix' }}
                </th>
                @foreach ($allActions as $action)
                    <th class="bg-base-200 min-w-20 px-2 text-center">
                        <div class="flex flex-col items-center gap-1">
                            <span class="text-xs font-medium">
                                {{ PermissionAction::getLabel($action) }}
                            </span>
                        </div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($matrixData as $entity => $supportedActions)
                <tr class="hover">
                    <td class="bg-base-100 sticky left-0 z-10 font-medium">
                        <div class="flex items-center gap-2">
                            <span>{{ PermissionEntity::getLabel($entity) }}</span>
                        </div>
                    </td>
                    @foreach ($allActions as $action)
                        @php
                            $isSupported = in_array($action, $supportedActions, true);
                            $permissionName = $matrix->getPermissionName($entity, $action);
                            $permission = $permissionLookup->get($permissionName);
                            $permissionId = $permission?->id;
                            $isChecked = $permissionId && in_array($permissionId, $selectedPermissions, false);
                        @endphp
                        <td class="px-2 text-center">
                            @if ($isSupported && $permission)
                                @if ($readonly)
                                    @if ($isChecked)
                                        <x-ui.icon name="check-circle"
                                                   class="text-success h-5 w-5"></x-ui.icon>
                                    @else
                                        <x-ui.icon name="x-circle"
                                                   class="text-base-content/30 h-5 w-5"></x-ui.icon>
                                    @endif
                                @else
                                    <input type="checkbox"
                                           class="checkbox checkbox-sm checkbox-primary"
                                           value="{{ $permissionId }}"
                                           @if ($wireModel) wire:model="{{ $wireModel }}" @endif
                                           @if ($isChecked) checked @endif
                                           title="{{ $permission->display_name ?? $permissionName }}" />
                                @endif
                            @else
                                <span class="text-base-content/20 inline-block h-5 w-5"
                                      title="{{ __('Not applicable') }}">
                                    —
                                </span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<style>
    .permission-matrix-container {
        max-height: 70vh;
    }

    .permission-matrix-container table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .permission-matrix-container thead th {
        position: sticky;
        top: 0;
        z-index: 20;
    }

    .permission-matrix-container thead th:first-child {
        z-index: 30;
    }

    .permission-matrix-container tbody td:first-child {
        border-right: 1px solid oklch(var(--bc) / 0.1);
    }
</style>
