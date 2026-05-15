<?php

use App\Enums\Ui\PlaceholderType;
use App\Livewire\Bases\BasePageComponent;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Trash\TrashedContext;
use App\Services\Trash\TrashRegistry;
use Illuminate\Database\Eloquent\Model;

new class extends BasePageComponent {
    public ?string $pageTitle = null;

    public ?string $pageSubtitle = null;

    protected PlaceholderType $placeholderType = PlaceholderType::CARD;

    protected int $placeholderRows = 3;

    public string $entityType = '';

    public ?Model $model = null;

    /** @var array<string, mixed> */
    public array $entityConfig = [];

    public bool $showForceDeleteModal = false;

    public string $confirmText = '';

    public string $activeTab = 'overview';

    /**
     * Mount the component.
     */
    public function mount(string $entityType, string $uuid): void
    {
        // Enable trashed context
        TrashedContext::enable($entityType);

        $registry = app(TrashRegistry::class);
        $config = $registry->getEntity($entityType);

        if (!$config) {
            abort(404);
        }

        $this->entityType = $entityType;
        $this->entityConfig = $config;

        // Check view permission
        $this->authorize($config['viewPermission']);

        // Find the trashed model
        $modelClass = $config['model'];
        $routeKey = \is_string($config['routeKey'] ?? null) ? $config['routeKey'] : 'uuid';
        $this->model = $modelClass::onlyTrashed()->where($routeKey, $uuid)->firstOrFail();

        $this->pageSubtitle = __('pages.trash.show.description');
    }

    public function getPageTitle(): string
    {
        return __('pages.trash.show.title', ['name' => $this->model?->label() ?? '']);
    }

    /**
     * Get tabs for the trashed item detail page.
     */
    public function tabs(): array
    {
        return [
            [
                'key' => 'overview',
                'label' => __('pages.trash.show.item_details'),
                'icon' => 'information-circle',
            ],
            [
                'key' => 'metadata',
                'label' => __('pages.trash.show.metadata'),
                'icon' => 'clock',
            ],
        ];
    }

    /**
     * Restore the trashed item.
     */
    public function restore(): void
    {
        $this->authorize($this->entityConfig['restorePermission']);

        $label = $this->model->label();
        $this->model->restore();

        NotificationBuilder::make()
            ->title('actions.restored_successfully', ['name' => $label])
            ->success()
            ->persist()
            ->send();

        $this->redirect(route('trash.index', ['entityType' => $this->entityType]), navigate: true);
    }

    /**
     * Force delete the item (requires type confirmation).
     */
    public function forceDelete(): void
    {
        $this->authorize($this->entityConfig['forceDeletePermission']);

        // Validate the confirmation text
        if (\trim($this->confirmText) !== \trim($this->model->label())) {
            NotificationBuilder::make()->title('pages.trash.show.confirm_mismatch')->error()->send();

            return;
        }

        $label = $this->model->label();
        $this->model->forceDelete();

        NotificationBuilder::make()
            ->title('actions.force_deleted_successfully', ['name' => $label])
            ->success()
            ->persist()
            ->send();

        $this->redirect(route('trash.index', ['entityType' => $this->entityType]), navigate: true);
    }

    /**
     * Open force delete modal.
     */
    public function openForceDeleteModal(): void
    {
        $this->confirmText = '';
        $this->showForceDeleteModal = true;
    }

    /**
     * Close force delete modal.
     */
    public function closeForceDeleteModal(): void
    {
        $this->showForceDeleteModal = false;
        $this->confirmText = '';
    }

    /**
     * Check if confirm text matches model label.
     */
    public function isConfirmValid(): bool
    {
        return \trim($this->confirmText) === \trim($this->model?->label() ?? '');
    }
}; ?>

<x-layouts.page backHref="{{ route('trash.index', ['entityType' => $entityType]) }}">
    <section class="mx-auto w-full max-w-4xl space-y-6"
             @confirm-restore.window="$wire.restore()">
        @if ($model)
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    {{-- Actions --}}
                    <div class="flex flex-wrap gap-2">
                        @can($entityConfig['restorePermission'])
                            <x-ui.button @click="confirmModal({
                                             title: @js(__('actions.restore')),
                                             message: @js(__('actions.confirm_restore')),
                                             confirmEvent: 'confirm-restore'
                                         })"
                                         color="success"
                                         size="sm"
                                         icon="arrow-uturn-left">
                                {{ __('actions.restore') }}
                            </x-ui.button>
                        @endcan

                        @can($entityConfig['forceDeletePermission'])
                            <x-ui.button wire:click="openForceDeleteModal"
                                         color="error"
                                         variant="outline"
                                         size="sm"
                                         icon="trash">
                                {{ __('actions.force_delete') }}
                            </x-ui.button>
                        @endcan
                    </div>
                </div>
            </div>

            <x-ui.tabs :tabs="$this->tabs()"
                       :active="$activeTab"
                       class="mb-6" />

            @if($activeTab === 'overview')
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70 border-b pb-2">{{ __('pages.trash.show.item_details') }}</x-ui.title>

                        <div class="space-y-3">
                            @if (isset($model->name))
                                <div>
                                    <span class="text-base-content/60 text-sm">{{ __('fields.name') }}</span>
                                    <p class="font-medium">{{ $model->name }}</p>
                                </div>
                            @endif

                            @if (isset($model->email))
                                <div>
                                    <span class="text-base-content/60 text-sm">{{ __('fields.email') }}</span>
                                    <p class="font-medium">{{ $model->email }}</p>
                                </div>
                            @endif

                            @if (isset($model->description))
                                <div>
                                    <span class="text-base-content/60 text-sm">{{ __('fields.description') }}</span>
                                    <p class="font-medium">{{ $model->description }}</p>
                                </div>
                            @endif

                            @if (isset($model->uuid))
                                <div>
                                    <span class="text-base-content/60 text-sm">{{ __('fields.uuid') }}</span>
                                    <p class="font-mono text-sm">{{ $model->uuid }}</p>
                                </div>
                            @elseif (isset($model->tenant_id))
                                <div>
                                    <span class="text-base-content/60 text-sm">{{ __('tenancy.tenant_id') }}</span>
                                    <p class="font-mono text-sm">{{ $model->tenant_id }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif($activeTab === 'metadata')
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body space-y-4">
                        <x-ui.title level="3"
                                    class="text-base-content/70 border-b pb-2">{{ __('pages.trash.show.metadata') }}</x-ui.title>

                        <div class="space-y-3">
                            <div>
                                <span class="text-base-content/60 text-sm">{{ __('fields.deleted_at') }}</span>
                                <p class="text-error font-medium">
                                    {{ $model->deleted_at->diffForHumans() }}
                                    <span
                                          class="text-base-content/60 text-sm">({{ $model->deleted_at->format('Y-m-d H:i') }})</span>
                                </p>
                            </div>

                            <div>
                                <span class="text-base-content/60 text-sm">{{ __('fields.created_at') }}</span>
                                <p class="font-medium">
                                    {{ $model->created_at->diffForHumans() }}
                                    <span
                                          class="text-base-content/60 text-sm">({{ $model->created_at->format('Y-m-d H:i') }})</span>
                                </p>
                            </div>

                            @if ($model->updated_at)
                                <div>
                                    <span class="text-base-content/60 text-sm">{{ __('fields.updated_at') }}</span>
                                    <p class="font-medium">
                                        {{ $model->updated_at->diffForHumans() }}
                                        <span
                                              class="text-base-content/60 text-sm">({{ $model->updated_at->format('Y-m-d H:i') }})</span>
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="alert alert-error">
                <x-ui.icon name="exclamation-triangle"
                           size="sm"></x-ui.icon>
                <span>{{ __('pages.trash.show.not_found') }}</span>
            </div>
        @endif
    </section>
</x-layouts.page>
