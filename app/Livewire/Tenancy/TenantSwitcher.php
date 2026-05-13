<?php

declare(strict_types=1);

namespace App\Livewire\Tenancy;

use App\Livewire\Bases\LivewireBaseComponent;
use App\Livewire\Tables\ImpersonateUserTable;
use App\Models\Tenant;
use App\Models\User;
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

        $table = new ImpersonateUserTable();
        $user = User::where('uuid', $this->selectedUserUuid)->firstOrFail();
        $tenant = Tenant::find($tenantId);

        $table->performImpersonation($user, $tenant);
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
