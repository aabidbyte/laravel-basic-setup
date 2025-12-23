<?php

declare(strict_types=1);

namespace App\Services\DataTable\Transformers;

use App\Models\User;
use App\Services\DataTable\Contracts\TransformerInterface;

/**
 * Transformer for User models in DataTable responses
 */
class UserDataTableTransformer implements TransformerInterface
{
    /**
     * Transform user model for DataTable response
     *
     * @param  User  $user
     * @return array<string, mixed>
     */
    public function transform($user): array
    {
        // Get roles in team context
        $roles = [];
        if (method_exists($user, 'roles')) {
            $user->load('roles');
            $roles = $user->roles->map(function ($role) {
                return [
                    'name' => $role->name,
                    'display_name' => ucwords(str_replace('_', ' ', $role->name)),
                ];
            })->toArray();
        }

        return [
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username ?? null,
            'is_active' => $user->is_active,
            'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
            'last_login_at' => $user->last_login_at?->format('Y-m-d H:i:s'),
            'roles' => $roles,
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
