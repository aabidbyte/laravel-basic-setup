<?php

declare(strict_types=1);

namespace App\Livewire\Tenancy;

use App\Livewire\Bases\LivewireBaseComponent;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Tenancy\UserImpersonationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class TenantSwitcher extends LivewireBaseComponent
{
    /**
     * Data for tenant selection prompt.
     */
    public array $tenants = [];

    public ?string $selectedUserUuid = null;

    public bool $showSelectionModal = false;

    protected $listeners = [
        'prompt-tenant-selection' => 'showTenantSelection',
    ];

    /**
     * Show the tenant selection modal.
     */
    public function showTenantSelection(array $data): void
    {
        $this->selectedUserUuid = $data['user_uuid'];
        $this->tenants = $data['tenants'];
        $this->showSelectionModal = true;
    }

    /**
     * Confirm tenant selection and proceed with impersonation.
     */
    public function selectTenant(string $tenantId): void
    {
        $this->showSelectionModal = false;

        $user = User::where('uuid', $this->selectedUserUuid)->firstOrFail();
        $tenant = Tenant::find($tenantId);

        if ($tenant === null || ! $user->tenants->contains('id', $tenantId)) {
            $this->dispatch('notify', ['type' => 'error', 'message' => __('tenancy.permission_denied')]);

            return;
        }

        $actor = Auth::user();
        if (! $actor instanceof User) {
            return;
        }

        try {
            $result = app(UserImpersonationService::class)->execute($actor, $user, $tenant);
        } catch (AuthorizationException) {
            $this->dispatch('notify', ['type' => 'error', 'message' => __('tenancy.permission_denied')]);

            return;
        }

        if ($result['type'] === 'tenant') {
            $this->redirect($result['url']);

            return;
        }

        $this->redirect('/dashboard');
    }

    /**
     * Get the current active tenant.
     */
    #[Computed]
    public function currentTenant(): ?Tenant
    {
        return tenant();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.tenancy.tenant-switcher');
    }
}
