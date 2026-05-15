<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\PolicyAbilities;
use App\Constants\DataTable\DataTableUi;
use App\Enums\ErrorHandling\ErrorActorType;
use App\Livewire\DataTable\Datatable;
use App\Models\ErrorLog;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Tenancy\TenantMembershipQuery;
use App\Support\Tenancy\TenantAudience;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * DataTable for managing error logs.
 *
 * Displays application errors with filtering, sorting, and actions.
 */
class ErrorLogTable extends Datatable
{
    public const CENTRAL_LOGS_FILTER = TenantMembershipQuery::CENTRAL_RECORDS_FILTER;

    /**
     * Mount the component and authorize access
     */
    public function mount(): void
    {
        // Authorize access to the list
        $this->authorize(PolicyAbilities::VIEW_ANY, ErrorLog::class);
    }

    /**
     * Define the base query for the table.
     */
    public function baseQuery(): Builder
    {
        $query = ErrorLog::query()
            ->with(['tenant', 'user'])
            ->select('error_logs.*');

        if (tenant() !== null) {
            $query->where(function (Builder $query): void {
                $query->where('tenant_id', tenant()->getTenantKey())
                    ->orWhereNull('tenant_id');
            });

            return $query;
        }

        return $this->tenantMembershipQuery()->applyToTenantKey(
            $query,
            $this->tenantAudience(),
            'error_logs.tenant_id',
        );
    }

    /**
     * Get column definitions
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make(__('errors.management.reference_id'), 'reference_id')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => "<code class=\"text-xs font-mono\">{$value}</code>")
                ->html(),

            Column::make(__('errors.management.exception'), 'exception_class')
                ->sortable()
                ->format(fn ($value) => class_basename($value))
                ->class('text-base-content/80'),

            Column::make(__('errors.management.tenant'), 'tenant_name')
                ->sortable()
                ->searchable()
                ->format(fn ($value, ErrorLog $row) => $this->formatTenant($row))
                ->html(),

            Column::make(__('errors.management.message'), 'message')
                ->searchable()
                ->width('240px'),

            Column::make(__('errors.management.url'), 'url')
                ->format(fn ($value, $row) => $this->formatUrl($value, $row->method))
                ->html(),

            Column::make(__('errors.management.actor'), 'actor_name')
                ->sortable()
                ->searchable()
                ->format(fn ($value, ErrorLog $row) => $this->formatActor($row))
                ->html(),

            Column::make(__('errors.management.runtime'), 'runtime_context')
                ->sortable()
                ->format(fn ($value, ErrorLog $row) => $this->formatRuntime($row))
                ->html()
                ->class('text-base-content/70'),

            Column::make(__('errors.management.status'), 'resolved_at')
                ->sortable()
                ->format(fn ($value) => $value
                    ? DataTableUi::renderComponent(DataTableUi::UI_BADGE, __('errors.management.resolved'), ['color' => 'success', 'size' => 'sm'])
                    : DataTableUi::renderComponent(DataTableUi::UI_BADGE, __('errors.management.unresolved'), ['color' => 'error', 'size' => 'sm']))
                ->html(),

            Column::make(__('errors.management.created_at'), 'created_at')
                ->sortable()
                ->format(fn ($value) => $value?->diffForHumans() ?? '-')
                ->class('text-base-content/60 text-sm'),
        ];
    }

    /**
     * Format URL with method badge.
     */
    protected function formatUrl(?string $url, ?string $method): string
    {
        if (! $url) {
            return '-';
        }

        $methodBadge = $method
            ? DataTableUi::renderComponent(DataTableUi::UI_BADGE, e($method), ['variant' => 'ghost', 'size' => 'xs', 'class' => 'mr-1'])
            : '';

        // Truncate long URLs
        $displayUrl = \strlen($url) > 40 ? \substr($url, 0, 40) . '...' : $url;

        return $methodBadge . '<span class="text-xs">' . e($displayUrl) . '</span>';
    }

    protected function formatTenant(ErrorLog $errorLog): string
    {
        if (! $errorLog->tenant_id) {
            return DataTableUi::renderComponent(DataTableUi::UI_BADGE, __('errors.management.central'), ['variant' => 'ghost', 'size' => 'sm']);
        }

        $label = $errorLog->tenant_name ?: $errorLog->tenant?->label() ?: $errorLog->tenant_id;
        $domain = $errorLog->tenant_domain ? '<span class="text-base-content/50 block text-[10px]">' . e($errorLog->tenant_domain) . '</span>' : '';

        return '<span class="text-sm font-medium">' . e($label) . '</span>' . $domain;
    }

    protected function formatActor(ErrorLog $errorLog): string
    {
        $actorType = $errorLog->actor_type;
        $label = $errorLog->actorLabel();
        $email = $errorLog->actor_email ? '<span class="text-base-content/50 block text-[10px]">' . e($errorLog->actor_email) . '</span>' : '';
        $badge = $actorType instanceof ErrorActorType
            ? DataTableUi::renderComponent(DataTableUi::UI_BADGE, $actorType->label(), ['color' => $actorType->color(), 'size' => 'xs', 'class' => 'mb-1'])
            : '';

        return $badge . '<span class="block text-sm font-medium">' . e($label) . '</span>' . $email;
    }

    protected function formatRuntime(ErrorLog $errorLog): string
    {
        return DataTableUi::renderComponent(DataTableUi::UI_BADGE, $errorLog->runtimeContextLabel(), ['variant' => 'ghost', 'size' => 'sm']);
    }

    /**
     * Get filter definitions
     *
     * @return array<int, Filter>
     */
    protected function getFilterDefinitions(): array
    {
        return [
            Filter::make('status', __('errors.management.status'))
                ->placeholder(__('errors.management.all_status'))
                ->type('select')
                ->options([
                    'unresolved' => __('errors.management.unresolved'),
                    'resolved' => __('errors.management.resolved'),
                ])
                ->execute(fn ($query, $value) => $value === 'unresolved'
                    ? $query->whereNull('resolved_at')
                    : $query->whereNotNull('resolved_at')),

            Filter::make('date_range', __('errors.management.date_range'))
                ->placeholder(__('errors.management.all_dates'))
                ->type('select')
                ->options([
                    'today' => __('errors.management.today'),
                    '7_days' => __('errors.management.last_7_days'),
                    '30_days' => __('errors.management.last_30_days'),
                ])
                ->execute(fn ($query, $value) => match ($value) {
                    'today' => $query->whereDate('created_at', today()),
                    '7_days' => $query->where('created_at', '>=', now()->subDays(7)),
                    '30_days' => $query->where('created_at', '>=', now()->subDays(30)),
                    default => $query,
                }),

            Filter::make('exception_class', __('errors.management.exception'))
                ->placeholder(__('errors.management.all_exceptions'))
                ->type('select')
                ->options($this->getExceptionTypeOptions()),

            Filter::make('tenant_id', __('errors.management.tenant'))
                ->placeholder(__('errors.management.all_tenants'))
                ->type('select')
                ->options($this->getTenantOptions())
                ->execute(function (): void {
                    // Tenant filtering is applied in baseQuery() so central-only
                    // can replace the default tenant-member audience.
                })
                ->show(fn () => tenant() === null && $this->currentActor() instanceof User),

            Filter::make('actor_type', __('errors.management.actor_type'))
                ->placeholder(__('errors.management.all_actor_types'))
                ->type('select')
                ->options($this->getActorTypeOptions()),

            Filter::make('runtime_context', __('errors.management.runtime'))
                ->placeholder(__('errors.management.all_runtimes'))
                ->type('select')
                ->options([
                    'http' => __('errors.management.runtime_contexts.http'),
                    'console' => __('errors.management.runtime_contexts.console'),
                    'queue' => __('errors.management.runtime_contexts.queue'),
                ]),
        ];
    }

    /**
     * Get unique exception types for filter (memoized).
     *
     * @return array<string, string>
     */
    protected function getExceptionTypeOptions(): array
    {
        return $this->memoize('filter:exception_types', fn () => ErrorLog::query()
            ->distinct()
            ->pluck('exception_class')
            ->mapWithKeys(fn ($class) => [$class => class_basename($class)])
            ->toArray());
    }

    /**
     * @return array<string, string>
     */
    protected function getTenantOptions(): array
    {
        return $this->memoize('filter:tenants', function (): array {
            $options = Tenant::query()
                ->orderBy('name')
                ->get(['tenant_id', 'name'])
                ->mapWithKeys(fn (Tenant $tenant) => [$tenant->tenant_id => $tenant->label()])
                ->toArray();

            if (! $this->currentActor()?->isSuperAdmin()) {
                $tenantIds = $this->currentActor()?->tenants()->pluck('tenants.tenant_id')->all() ?? [];

                return \array_intersect_key($options, \array_flip($tenantIds));
            }

            return [self::CENTRAL_LOGS_FILTER => __('errors.management.central')] + $options;
        });
    }

    /**
     * @return array<string, string>
     */
    protected function getActorTypeOptions(): array
    {
        return collect(ErrorActorType::cases())
            ->mapWithKeys(fn (ErrorActorType $actorType) => [$actorType->value => $actorType->label()])
            ->toArray();
    }

    /**
     * Get row action definitions
     *
     * @return array<int, Action>
     */
    protected function rowActions(): array
    {
        $actions = [];

        // View action
        if (Route::has('admin.errors.show')) {
            $actions[] = Action::make('view', __('actions.view'))
                ->icon('eye')
                ->route(fn (ErrorLog $errorLog) => route('admin.errors.show', $errorLog->uuid))
                ->variant('ghost')
                ->color('info')
                ->can(PolicyAbilities::VIEW);
        }

        // Resolve action (only for unresolved errors)
        $actions[] = Action::make('resolve', __('errors.management.resolve_confirm'))
            ->icon('check')
            ->variant('ghost')
            ->color('success')
            ->confirm(__('errors.management.confirm_resolve'))
            ->execute(function (ErrorLog $errorLog) {
                $errorLog->resolve([
                    'resolver_id' => Auth::id(),
                    'resolver_name' => Auth::user()?->name,
                ]);
                NotificationBuilder::make()
                    ->title('errors.management.resolve_success')
                    ->success()
                    ->send();
            })
            ->show(fn (ErrorLog $errorLog) => ! $errorLog->isResolved())
            ->can('resolve');

        // Delete action
        $actions[] = Action::make('delete', __('actions.delete'))
            ->icon('trash')
            ->variant('ghost')
            ->color('error')
            ->confirm(__('errors.management.confirm_delete'))
            ->execute(function (ErrorLog $errorLog) {
                $errorLog->delete();
                NotificationBuilder::make()
                    ->title('errors.management.deleted_successfully')
                    ->success()
                    ->send();
            })
            ->can(PolicyAbilities::DELETE);

        return $actions;
    }

    /**
     * Get bulk action definitions
     *
     * @return array<int, BulkAction>
     */
    protected function bulkActions(): array
    {
        return [
            BulkAction::make('resolve', __('errors.management.resolve_selected'))
                ->icon('check')
                ->variant('ghost')
                ->color('success')
                ->confirm(__('errors.management.confirm_bulk_resolve'))
                ->execute(function ($errorLogs) {
                    $count = $errorLogs->filter(fn ($e) => ! $e->isResolved())->count();
                    $errorLogs->each(fn (ErrorLog $e) => $e->resolve([
                        'resolver_id' => Auth::id(),
                        'resolver_name' => Auth::user()?->name,
                    ]));
                    NotificationBuilder::make()
                        ->title('errors.management.bulk_resolved_successfully', ['count' => $count])
                        ->success()
                        ->send();
                })
                ->can('resolve'),

            BulkAction::make('delete', __('errors.management.delete_selected'))
                ->icon('trash')
                ->variant('ghost')
                ->color('error')
                ->confirm(__('errors.management.confirm_bulk_delete'))
                ->execute(function ($errorLogs) {
                    $count = $errorLogs->count();
                    $errorLogs->each->delete();
                    NotificationBuilder::make()
                        ->title('errors.management.bulk_deleted_successfully', ['count' => $count])
                        ->success()
                        ->send();
                })
                ->can(PolicyAbilities::DELETE),
        ];
    }

    /**
     * Handle row click
     */
    public function rowClick(string $uuid): ?Action
    {
        if (Route::has('admin.errors.show')) {
            return Action::make()
                ->route('admin.errors.show', $uuid)
                ->can(PolicyAbilities::VIEW);
        }

        return null;
    }

    protected function tenantAudience(): TenantAudience
    {
        $audience = TenantAudience::visibleTo($this->currentActor());
        $tenantFilter = $this->selectedTenantFilter();

        if ($tenantFilter === null) {
            return $audience;
        }

        return $this->tenantMembershipQuery()->audienceFromFilter($audience, $tenantFilter);
    }

    protected function currentActor(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    protected function selectedTenantFilter(): ?string
    {
        $tenantFilter = $this->filters['tenant_id'] ?? null;

        return \is_string($tenantFilter) && $tenantFilter !== '' ? $tenantFilter : null;
    }

    protected function tenantMembershipQuery(): TenantMembershipQuery
    {
        return app(TenantMembershipQuery::class);
    }
}
