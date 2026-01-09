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

    $matrix = new PermissionMatrix();
    $matrixData = $matrix->getMatrix();
    $allActions = $matrix->getAllActions();

    // Create a lookup map: permission name => permission model
    $permissionLookup = $permissions->keyBy('name');
@endphp

<div class="permission-matrix-container overflow-x-auto">
    <table class="table table-zebra table-compact w-full">
        <thead>
            <tr>
                <th class="bg-base-200 sticky left-0 z-10 min-w-48">
                    {{ __('permissions.entities.users') ? __('permissions.matrix.title') : 'Permission Matrix' }}
                </th>
                @foreach ($allActions as $action)
                    <th class="text-center bg-base-200 px-2 min-w-20">
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
                    <td class="font-medium bg-base-100 sticky left-0 z-10">
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
                        <td class="text-center px-2">
                            @if ($isSupported && $permission)
                                @if ($readonly)
                                    @if ($isChecked)
                                        <x-ui.icon
                                            name="check-circle"
                                            class="w-5 h-5 text-success"
                                        ></x-ui.icon>
                                    @else
                                        <x-ui.icon
                                            name="x-circle"
                                            class="w-5 h-5 text-base-content/30"
                                        ></x-ui.icon>
                                    @endif
                                @else
                                    <input
                                        type="checkbox"
                                        class="checkbox checkbox-sm checkbox-primary"
                                        value="{{ $permissionId }}"
                                        @if ($wireModel) wire:model="{{ $wireModel }}" @endif
                                        @if ($isChecked) checked @endif
                                        title="{{ $permission->display_name ?? $permissionName }}"
                                    />
                                @endif
                            @else
                                <span
                                    class="inline-block w-5 h-5 text-base-content/20"
                                    title="{{ __('Not applicable') }}"
                                >
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
