<?php

declare(strict_types=1);

namespace App\Livewire\Tables\EmailTemplate;

use App\Constants\Auth\Permissions;
use App\Constants\DataTable\DataTableUi;
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
     * Mount the component and authorize access.
     */
    public function mount(): void
    {
        $this->authorize(Permissions::VIEW_EMAIL_TEMPLATES);
    }

    /**
     * Define the base query for the table.
     */
    public function baseQuery(): Builder
    {
        return EmailTemplate::query()
            ->with(['layout', 'translations'])
            ->select('email_templates.*');
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

            Column::make(__('table.email_templates.kind'), 'is_layout')
                ->sortable()
                ->content(fn (EmailTemplate $template) => [$template->is_layout
                    ? __('email_templates.kind.layout')
                    : __('email_templates.kind.content')])
                ->type(DataTableUi::UI_BADGE, [
                    'color' => fn (EmailTemplate $template) => $template->is_layout ? 'info' : 'primary',
                    'size' => 'sm',
                ]),

            Column::make(__('table.email_templates.type'), 'type')
                ->sortable()
                ->content(fn (EmailTemplate $template) => [$template->type->label()])
                ->type(DataTableUi::UI_BADGE, ['color' => 'neutral', 'size' => 'sm']),

            Column::make(__('table.email_templates.layout'), 'layout_id')
                ->content(fn (EmailTemplate $template) => $template->layout?->name ?? '—'),

            Column::make(__('table.email_templates.status'), 'status')
                ->sortable()
                ->content(fn (EmailTemplate $template) => [$template->status->label()])
                ->type(DataTableUi::UI_BADGE, ['color' => fn (EmailTemplate $template) => $template->status->color(), 'size' => 'sm']),

            Column::make(__('table.email_templates.translations'), 'translations_count')
                ->content(fn (EmailTemplate $template) => $template->translations->count() . ' ' . __('common.locales')),

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
            Filter::make('is_layout', __('table.email_templates.filters.kind'))
                ->placeholder(__('table.email_templates.filters.all_kinds'))
                ->type('select')
                ->options([
                    '0' => __('email_templates.kind.content'),
                    '1' => __('email_templates.kind.layout'),
                ])
                ->valueMapping(['1' => true, '0' => false]),

            Filter::make('type', __('table.email_templates.filters.type'))
                ->placeholder(__('table.email_templates.filters.all_types'))
                ->type('select')
                ->options(
                    collect(EmailTemplateType::cases())
                        ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                        ->toArray(),
                ),

            Filter::make('status', __('table.email_templates.filters.status'))
                ->placeholder(__('table.email_templates.filters.all_statuses'))
                ->type('select')
                ->options(
                    collect(EmailTemplateStatus::cases())
                        ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                        ->toArray(),
                ),
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

        if (Route::has('emailTemplates.show')) {
            $actions[] = Action::make('view', __('actions.view'))
                ->icon('eye')
                ->route(fn (EmailTemplate $template) => route('emailTemplates.show', $template->id))
                ->variant('ghost')
                ->can(Permissions::VIEW_EMAIL_TEMPLATES, false);
        }

        if (Route::has('emailTemplates.edit')) {
            $actions[] = Action::make('edit', __('actions.edit'))
                ->icon('pencil')
                ->route(fn (EmailTemplate $template) => route('emailTemplates.edit', $template->id))
                ->variant('ghost')
                ->can(Permissions::EDIT_EMAIL_TEMPLATES, false);
        }

        $actions[] = Action::make('delete', __('actions.delete'))
            ->icon('trash')
            ->variant('ghost')
            ->color('error')
            ->confirm(__('actions.confirm_delete'))
            ->execute(function (EmailTemplate $template) {
                $this->deleteTemplate($template);
            })
            ->can(Permissions::DELETE_EMAIL_TEMPLATES, false)
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
                ->can(Permissions::PUBLISH_EMAIL_TEMPLATES),

            BulkAction::make('archive', __('email_templates.actions.archive'))
                ->icon('archive-box')
                ->variant('ghost')
                ->execute(fn ($templates) => $templates->each->update(['status' => EmailTemplateStatus::ARCHIVED]))
                ->can(Permissions::EDIT_EMAIL_TEMPLATES),

            BulkAction::make('delete', __('actions.delete'))
                ->icon('trash')
                ->variant('ghost')
                ->color('error')
                ->confirm(__('actions.confirm_bulk_delete'))
                ->execute(fn ($templates) => $templates->where('is_system', false)->where('is_default', false)->each->delete())
                ->can(Permissions::DELETE_EMAIL_TEMPLATES),
        ];
    }
}
