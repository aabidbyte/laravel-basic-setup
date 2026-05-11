<?php

declare(strict_types=1);

namespace App\Livewire\Tenancy;

use App\Livewire\Bases\LivewireBaseComponent;
use App\Livewire\Tables\ImpersonateUserTable;
use App\Models\Tenant;
use App\Models\User;
use Livewire\Attributes\Computed;

class WorkspaceSwitcher extends LivewireBaseComponent
{
    /**
     * Data for workspace selection prompt.
     */
    public array $workspaces = [];
    public ?string $selectedUserUuid = null;
    public bool $showSelectionModal = false;

    protected $listeners = [
        'prompt-workspace-selection' => 'showWorkspaceSelection',
    ];

    /**
     * Show the workspace selection modal.
     */
    public function showWorkspaceSelection(array $data): void
    {
        $this->selectedUserUuid = $data['user_uuid'];
        $this->workspaces = $data['workspaces'];
        $this->showSelectionModal = true;
    }

    /**
     * Confirm workspace selection and proceed with impersonation.
     */
    public function selectWorkspace(string $tenantId): void
    {
        $this->showSelectionModal = false;

        $table = new \App\Livewire\Tables\ImpersonateUserTable();
        $user = \App\Models\User::where('uuid', $this->selectedUserUuid)->firstOrFail();
        $tenant = \App\Models\Tenant::find($tenantId);

        $table->performImpersonation($user, $tenant);
    }


    /**
     * Get the current active workspace.
     */
    #[Computed]
    public function currentWorkspace(): ?Tenant
    {
        return tenant();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.tenancy.workspace-switcher');
    }
}
