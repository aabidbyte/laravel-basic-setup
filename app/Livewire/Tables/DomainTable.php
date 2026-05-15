<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Livewire\DataTable\Datatable;
use App\Models\Domain;
use App\Models\Tenant;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class DomainTable extends Datatable
{
    /**
     * The tenant ID.
     */
    public string $tenantId;

    /**
     * Get the base query.
     */
    public function baseQuery(): Builder
    {
        return Domain::query()
            ->where('tenant_id', $this->tenantId)
            ->select(['domains.id', 'domains.domain', 'domains.tenant_id', 'domains.created_at', 'domains.id as uuid']);
    }

    /**
     * Define the columns for the table.
     */
    public function columns(): array
    {
        return [
            Column::make(__('tenancy.domain_name'), 'domain')
                ->searchable()
                ->sortable()
                ->format(fn ($value) => "<strong>{$value}</strong>")
                ->html(),

            Column::make(__('common.created_at'), 'created_at')
                ->format(fn ($value) => formatDateTime($value))
                ->sortable(),
        ];
    }

    /**
     * Define the row actions.
     */
    protected function rowActions(): array
    {
        return [
            Action::make('delete', __('actions.delete'))
                ->icon('trash')
                ->color('error')
                ->confirm(__('tenancy.confirm_delete_domain'))
                ->execute(fn (Domain $domain) => $this->deleteDomain($domain)),
        ];
    }

    /**
     * Delete a domain.
     */
    public function deleteDomain(Domain $domain): void
    {
        $tenant = Tenant::where('tenant_id', $this->tenantId)->first();

        if (! $tenant instanceof Tenant) {
            return;
        }

        // Custom authorization check for domains
        if (! Gate::allows('delete', $tenant)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('errors.unauthorized'),
            ]);

            return;
        }

        if ($tenant->domains()->count() <= 1) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('tenancy.cannot_delete_last_domain'),
            ]);

            return;
        }

        $domain->delete();

        NotificationBuilder::make()
            ->title('tenancy.domain_deleted')
            ->success()
            ->send();

        $this->refreshTable();
    }
}
