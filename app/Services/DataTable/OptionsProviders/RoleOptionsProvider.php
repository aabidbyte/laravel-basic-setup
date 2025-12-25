<?php

declare(strict_types=1);

namespace App\Services\DataTable\OptionsProviders;

use App\Services\DataTable\Contracts\OptionsProviderInterface;
use Spatie\Permission\Models\Role;

/**
 * Options provider for role filter
 */
class RoleOptionsProvider implements OptionsProviderInterface
{
    /**
     * Get options array for role select filter
     *
     * @return array<string, string> Associative array (value => label)
     */
    public function getOptions(): array
    {
        return Role::select('name')->get()->mapWithKeys(function ($role) {
            return [
                $role->name => ucwords(str_replace('_', ' ', $role->name)),
            ];
        })->toArray();
    }
}
