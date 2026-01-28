<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\PolicyAbilities;
use App\Constants\DataTable\DataTableUi;
use App\Livewire\DataTable\Datatable;
use App\Models\ErrorLog;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use App\Services\Notifications\NotificationBuilder;
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
        return ErrorLog::query()->with('user')->select('error_logs.*');
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

            Column::make(__('errors.management.message'), 'message')
                ->searchable()
                ->width('200px'),

            Column::make(__('errors.management.url'), 'url')
                ->format(fn ($value, $row) => $this->formatUrl($value, $row->method))
                ->html(),

            Column::make(__('errors.management.user'), 'user_name')
                ->label(fn ($row) => $row->user?->name ?? __('errors.management.guest'))
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
        $displayUrl = strlen($url) > 40 ? substr($url, 0, 40) . '...' : $url;

        return $methodBadge . '<span class="text-xs">' . e($displayUrl) . '</span>';
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
                ->placeholder(__('errors.management.all_status'))
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
}
