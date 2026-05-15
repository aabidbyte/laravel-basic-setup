<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Constants\Auth\PolicyAbilities;
use App\Livewire\DataTable\Datatable;
use App\Models\Feature;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use App\Services\Features\FeatureValueNormalizer;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;

class FeatureTable extends Datatable
{
    public ?string $title = 'features.list_title';

    public bool $showSearch = true;

    public string $sortBy = 'key';

    public string $sortDirection = 'asc';

    public function mount(): void
    {
        $this->authorize(PolicyAbilities::VIEW_ANY, Feature::class);
    }

    public function baseQuery(): Builder
    {
        return Feature::query()
            ->select(['features.*']);
    }

    public function columns(): array
    {
        return [
            Column::make(__('features.fields.name'), 'name')
                ->searchable()
                ->format(fn ($value, Feature $feature) => "<strong>{$feature->label()}</strong>")
                ->html(),

            Column::make(__('features.fields.key'), 'key')
                ->searchable()
                ->sortable(),

            Column::make(__('features.fields.type'), 'type')
                ->format(fn ($value) => $value->label())
                ->badge(fn () => 'info'),

            Column::make(__('features.fields.default_value'), 'default_value')
                ->format(fn ($value) => app(FeatureValueNormalizer::class)->display($value)),

            Column::make(__('features.fields.is_active'), 'is_active')
                ->format(fn ($value) => $value ? __('common.active') : __('common.inactive'))
                ->badge(fn (Feature $feature) => $feature->is_active ? 'success' : 'ghost'),
        ];
    }

    protected function rowActions(): array
    {
        return [
            Action::make('edit', __('actions.edit'))
                ->icon('pencil')
                ->variant('ghost')
                ->color('primary')
                ->route(fn (Feature $feature) => route('features.edit', $feature))
                ->can(PolicyAbilities::UPDATE),

            Action::make('delete', __('actions.delete'))
                ->icon('trash')
                ->variant('ghost')
                ->color('error')
                ->confirm(__('features.delete_confirm'))
                ->execute(function (Feature $feature): void {
                    $feature->delete();

                    NotificationBuilder::make()
                        ->title('features.deleted_successfully', ['name' => $feature->label()])
                        ->success()
                        ->send();
                })
                ->can(PolicyAbilities::DELETE),
        ];
    }

    public function rowClick(string $uuid): ?Action
    {
        if (Route::has('features.edit')) {
            return Action::make()
                ->route('features.edit', $uuid)
                ->can(PolicyAbilities::UPDATE);
        }

        return null;
    }
}
