<?php

declare(strict_types=1);

namespace App\Services\DataTable\Configs;

use App\Services\DataTable\Contracts\DataTableConfigInterface;
use App\Services\DataTable\OptionsProviders\RoleOptionsProvider;

/**
 * DataTable configuration for Users
 */
class UsersDataTableConfig implements DataTableConfigInterface
{
    public function getSearchableFields(): array
    {
        return ['name', 'email', 'username'];
    }

    public function getFilterableFields(): array
    {
        return [
            'role' => [
                'type' => 'select',
                'label' => __('ui.table.users.filters.role'),
                'placeholder' => __('ui.table.users.filters.all_roles'),
                'options_provider' => RoleOptionsProvider::class,
                'relationship' => [
                    'name' => 'roles',
                    'column' => 'name',
                ],
            ],
            'is_active' => [
                'type' => 'select',
                'label' => __('ui.table.users.filters.status'),
                'placeholder' => __('ui.table.users.filters.all_status'),
                'options' => [
                    ['value' => '1', 'label' => __('ui.table.users.status_active')],
                    ['value' => '0', 'label' => __('ui.table.users.status_inactive')],
                ],
                'value_mapping' => [
                    '1' => true,
                    '0' => false,
                ],
            ],
            'email_verified_at' => [
                'type' => 'select',
                'label' => __('ui.table.users.filters.verified'),
                'placeholder' => __('ui.table.users.filters.all_status'),
                'options' => [
                    ['value' => '1', 'label' => __('ui.table.users.verified_yes')],
                    ['value' => '0', 'label' => __('ui.table.users.verified_no')],
                ],
                'field_mapping' => 'email_verified_at',
                'value_mapping' => [
                    '1' => 'not_null',
                    '0' => 'null',
                ],
            ],
            'created_at' => [
                'type' => 'date_range',
                'label' => __('ui.table.users.filters.created_at'),
                'column' => 'created_at',
            ],
        ];
    }

    public function getSortableFields(): array
    {
        return [
            'name' => ['label' => __('ui.table.users.name')],
            'email' => ['label' => __('ui.table.users.email')],
            'is_active' => ['label' => __('ui.table.users.status')],
            'email_verified_at' => ['label' => __('ui.table.users.verified')],
            'created_at' => ['label' => __('ui.table.users.created_at')],
        ];
    }

    public function getDefaultSort(): ?array
    {
        return [
            'column' => 'created_at',
            'direction' => 'desc',
        ];
    }

    public function includeConfig(): bool
    {
        return true;
    }

    public function includeFilterState(): bool
    {
        return true;
    }

    public function getBulkActions(): array
    {
        return [
            ['key' => 'activate', 'label' => __('ui.actions.activate_selected'), 'variant' => 'ghost'],
            ['key' => 'deactivate', 'label' => __('ui.actions.deactivate_selected'), 'variant' => 'ghost'],
            ['key' => 'delete', 'label' => __('ui.actions.delete_selected'), 'variant' => 'ghost', 'color' => 'error'],
        ];
    }

    public function getEntityKey(): string
    {
        return 'users';
    }

    public function getViewName(): ?string
    {
        return null; // Not needed for Livewire
    }
}
