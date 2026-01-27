<?php

declare(strict_types=1);

namespace App\Livewire\Tables\EmailTemplate;

use App\Constants\Auth\Permissions;
use App\Constants\DataTable\DataTableUi;
use App\Enums\EmailTemplate\EmailTemplateKind;
use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Enums\EmailTemplate\EmailTemplateType;
use App\Livewire\DataTable\Datatable;
use App\Models\EmailTemplate\EmailTemplate;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;

class EmailTemplateTable extends Datatable
{
    /**
     * Context mode (layout, content, or null for all).
     */
    public ?EmailTemplateKind $kindMode = null;

    /**
     * Mount the component and authorize access.
     */
    public function mount(): void
    {
        $this->authorize(Permissions::VIEW_EMAIL_TEMPLATES());

        // Fallback detection if not passed as a prop
        if ($this->kindMode === null) {
            $this->kindMode = $this->detectKindMode();
        }
    }

    public function baseQuery(): Builder
    {
        return EmailTemplate::query()
            ->with(['layout', 'translations'])
            ->when($this->kindMode !== null, fn ($q) => $q->where('email_templates.is_layout', $this->kindMode->isLayout()))
            ->select('email_templates.*');
    }

    /**
     * Handle row click action.
     * Navigate to the show page when a row is clicked.
     */
    public function rowClick(string $uuid): ?Action
    {
        if (Route::has('emailTemplates.show')) {
            return Action::make()
                ->route(fn (EmailTemplate $template) => route('emailTemplates.show', $template));
        }

        return null;
    }

    /**
     * Detect kind mode based on route.
     * Only works during initial request.
     */
    protected function detectKindMode(): ?EmailTemplateKind
    {
        if (Route::is('emailTemplates.layouts.*')) {
            return EmailTemplateKind::LAYOUT;
        }

        if (Route::is('emailTemplates.contents.*')) {
            return EmailTemplateKind::CONTENT;
        }

        return null;
    }

    /**
     * Get the datatable identifier (full class name).
     * Overridden to separate preferences for Layout vs Content/All modes.
     */
    protected function getPersonalisedDatatableIdentifier(): string
    {
        return $this->kindMode?->value ?? 'all';
    }

    /**
     * Get column definitions.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make(__('table.email_templates.name'), 'name')
                ->sortable()
                ->searchable()
                ->format(fn ($value) => "<strong>{$value}</strong>")
                ->html(),

            Column::make(__('table.email_templates.type'), 'type')
                ->sortable()
                ->content(fn (EmailTemplate $template) => [$template->type->label()])
                ->type(DataTableUi::UI_BADGE, ['color' => 'neutral', 'size' => 'sm']),

            Column::make(__('table.email_templates.layout'), 'layout.name')
                ->content(fn (EmailTemplate $template) => $template->layout?->name ?? '—')
                ->hidden(fn () => $this->kindMode === EmailTemplateKind::LAYOUT),

            Column::make(__('table.email_templates.status'), 'status')
                ->sortable()
                ->content(fn (EmailTemplate $template) => [$template->status->label()])
                ->type(DataTableUi::UI_BADGE, ['color' => fn (EmailTemplate $template) => $template->status->color(), 'size' => 'sm']),

            Column::make(__('table.common.updated_at'), 'updated_at')
                ->sortable()
                ->format(fn ($value) => $value?->diffForHumans() ?? '—'),
        ];
    }

    /**
     * Get filter definitions.
     *
     * @return array<int, Filter>
     */
    protected function getFilterDefinitions(): array
    {
        return [
            Filter::make('layout_id', __('table.email_templates.filters.layout'))
                ->placeholder(__('table.email_templates.filters.all_layouts'))
                ->type('select')
                ->options(
                    EmailTemplate::layouts()
                        ->pluck('name', 'id')
                        ->toArray(),
                )
                ->fieldMapping('email_templates.layout_id')
                ->show(fn () => $this->kindMode === EmailTemplateKind::CONTENT),

            Filter::make('type', __('table.email_templates.filters.type'))
                ->placeholder(__('table.email_templates.filters.all_types'))
                ->type('select')
                ->options(
                    collect(EmailTemplateType::cases())
                        ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                        ->toArray(),
                )
                ->fieldMapping('email_templates.type'),

            Filter::make('status', __('table.email_templates.filters.status'))
                ->placeholder(__('table.email_templates.filters.all_statuses'))
                ->type('select')
                ->options(
                    collect(EmailTemplateStatus::cases())
                        ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                        ->toArray(),
                )
                ->fieldMapping('email_templates.status'),
        ];
    }

    /**
     * Get row action definitions.
     *
     * @return array<int, Action>
     */
    protected function rowActions(): array
    {
        $actions = [];

        // Preview action
        $actions[] = Action::make('preview', __('actions.preview'))
            ->icon('eye')
            ->variant('ghost')
            ->livewireModal('emailTemplates.preview', fn (EmailTemplate $template) => ['templateUuid' => $template->uuid])
            ->can(Permissions::VIEW_EMAIL_TEMPLATES(), false);

        if (Route::has('emailTemplates.show')) {
            $actions[] = Action::make('view', __('actions.view'))
                ->icon('document-text')
                ->route(fn (EmailTemplate $template) => route('emailTemplates.show', $template))
                ->variant('ghost')
                ->can(Permissions::VIEW_EMAIL_TEMPLATES(), false);
        }

        if (Route::has('emailTemplates.settings.edit')) {
            $actions[] = Action::make('edit', __('actions.edit'))
                ->icon('pencil')
                ->route(fn (EmailTemplate $template) => route('emailTemplates.settings.edit', $template))
                ->variant('ghost')
                ->can(Permissions::EDIT_EMAIL_TEMPLATES(), false);
        }

        $actions[] = Action::make('publish', __('email_templates.actions.publish'))
            ->icon('check-circle')
            ->variant('ghost')
            ->color('success')
            ->confirm(__('actions.confirm_publish'))
            ->execute(fn (EmailTemplate $template) => $template->update(['status' => EmailTemplateStatus::PUBLISHED]))
            ->can(Permissions::EDIT_EMAIL_TEMPLATES(), false) // Using EDIT permission as PUBLISH might be overkill or same
            ->show(fn (EmailTemplate $template) => ! $template->is_system && ! $template->is_default && $template->status === EmailTemplateStatus::DRAFT);

        $actions[] = Action::make('archive', __('email_templates.actions.archive'))
            ->icon('archive-box')
            ->variant('ghost')
            ->confirm(__('actions.confirm_archive'))
            ->execute(fn (EmailTemplate $template) => $template->update(['status' => EmailTemplateStatus::ARCHIVED]))
            ->can(Permissions::EDIT_EMAIL_TEMPLATES(), false)
            ->show(fn (EmailTemplate $template) => ! $template->is_system && ! $template->is_default && $template->status !== EmailTemplateStatus::ARCHIVED);

        $actions[] = Action::make('delete', __('actions.delete'))
            ->icon('trash')
            ->variant('ghost')
            ->color('error')
            ->confirm(__('actions.confirm_delete'))
            ->execute(function (EmailTemplate $template) {
                $this->deleteTemplate($template);
            })
            ->can(Permissions::DELETE_EMAIL_TEMPLATES(), false)
            ->show(fn (EmailTemplate $template) => ! $template->is_system && ! $template->is_default);

        return $actions;
    }

    /**
     * Delete a template with system/default check.
     */
    protected function deleteTemplate(EmailTemplate $template): void
    {
        if ($template->is_system) {
            NotificationBuilder::make()
                ->title(__('email_templates.cannot_delete_system'))
                ->error()
                ->send();

            return;
        }

        if ($template->is_default) {
            NotificationBuilder::make()
                ->title(__('email_templates.cannot_delete_default'))
                ->error()
                ->send();

            return;
        }

        $template->delete();
        NotificationBuilder::make()
            ->title(__('actions.deleted_successfully', ['name' => $template->name]))
            ->success()
            ->send();
    }

    /**
     * Get bulk action definitions.
     *
     * @return array<int, BulkAction>
     */
    protected function bulkActions(): array
    {
        return [
            BulkAction::make('publish', __('email_templates.actions.publish'))
                ->icon('check')
                ->variant('ghost')
                ->execute(fn ($templates) => $templates->each->update(['status' => EmailTemplateStatus::PUBLISHED]))
                ->can(Permissions::PUBLISH_EMAIL_TEMPLATES()),

            BulkAction::make('archive', __('email_templates.actions.archive'))
                ->icon('archive-box')
                ->variant('ghost')
                ->execute(fn ($templates) => $templates->each->update(['status' => EmailTemplateStatus::ARCHIVED]))
                ->can(Permissions::EDIT_EMAIL_TEMPLATES()),

            BulkAction::make('delete', __('actions.delete'))
                ->icon('trash')
                ->variant('ghost')
                ->color('error')
                ->confirm(__('actions.confirm_bulk_delete'))
                ->execute(fn ($templates) => $templates->where('is_system', false)->where('is_default', false)->each->delete())
                ->can(Permissions::DELETE_EMAIL_TEMPLATES()),
        ];
    }
}
