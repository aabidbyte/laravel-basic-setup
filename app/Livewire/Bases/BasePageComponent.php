<?php

namespace App\Livewire\Bases;

use App\Services\Notifications\NotificationBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;

/**
 * Base component for full-page Livewire components.
 *
 * Extends LivewireBaseComponent with page-specific functionality
 * like page title and subtitle handling.
 */
abstract class BasePageComponent extends LivewireBaseComponent
{
    public ?string $pageTitle = null;

    public ?string $pageSubtitle = null;

    /**
     * Indicates if component is in create mode (true) or edit mode (false).
     * Used by unified create/edit pages.
     */
    public bool $isCreateMode = true;

    public function rendering($view, $data = []): void
    {
        $this->sharePageTitle();
        $this->sharePageSubtitle();
    }

    public function updatedPageTitle(): void
    {
        $this->sharePageTitle();
    }

    public function updatedPageSubtitle(): void
    {
        $this->sharePageSubtitle();
    }

    protected function sharePageTitle(): void
    {
        View::share('pageTitle', $this->getPageTitle());
    }

    protected function sharePageSubtitle(): void
    {
        View::share('pageSubtitle', $this->getPageSubtitle());
    }

    public function getPageTitle(): string
    {
        if ($this->pageTitle === null || $this->pageTitle === '') {
            return config('app.name');
        }
        // If it looks like a translation key (contains dots), translate it
        if (str_contains($this->pageTitle, '.')) {
            return __($this->pageTitle);
        }

        return $this->pageTitle;
    }

    public function getPageSubtitle(): ?string
    {
        if ($this->pageSubtitle === null || $this->pageSubtitle === '') {
            return null;
        }

        // If it looks like a translation key (contains dots), translate it
        if (str_contains($this->pageSubtitle, '.')) {
            return __($this->pageSubtitle);
        }

        return $this->pageSubtitle;
    }

    // ========================================
    // Unified Create/Edit Pattern Helpers
    // ========================================

    /**
     * Initialize model for unified create/edit components.
     * Detects mode based on model presence and calls appropriate initialization.
     *
     * @param  Model|null  $model  The model to edit, or null for create mode
     * @param  callable  $loadCallback  Callback to load existing model data
     * @param  callable|null  $prepareCallback  Optional callback to prepare new model
     */
    protected function initializeUnifiedModel(
        ?Model $model,
        callable $loadCallback,
        ?callable $prepareCallback = null,
    ): void {
        if ($model) {
            $this->isCreateMode = false;
            $loadCallback($model);
        } else {
            $this->isCreateMode = true;
            if ($prepareCallback) {
                $prepareCallback();
            }
        }
    }

    /**
     * Send success notification after create or update.
     *
     * @param  Model  $model  The model that was created/updated
     * @param  string  $messageKey  Translation key for success message
     */
    protected function sendSuccessNotification(Model $model, string $messageKey): void
    {
        NotificationBuilder::make()
            ->title($messageKey, ['name' => $model->label()])
            ->success()
            ->persist()
            ->send();
    }

    /**
     * Optional label for the model type used in common translations.
     * e.g., __('types.user')
     */
    public ?string $modelTypeLabel = null;

    /**
     * Get submit button text based on mode.
     * Can be overridden for custom text.
     */
    public function getSubmitButtonTextProperty(): string
    {
        $params = $this->modelTypeLabel ? ['type' => $this->modelTypeLabel] : [];

        return $this->isCreateMode
            ? __('pages.common.create.submit', $params)
            : __('pages.common.edit.submit', $params);
    }

    /**
     * Get submit action name based on mode.
     */
    public function getSubmitActionProperty(): string
    {
        return $this->isCreateMode ? 'create' : 'save';
    }

    /**
     * Get cancel URL based on mode.
     * Override this method to customize cancel behavior.
     *
     * @param  string  $indexRoute  Route name for index page (used in create mode)
     * @param  string|null  $showRoute  Route name for show page (used in edit mode)
     * @param  Model|null  $model  Model instance (required for edit mode if using show route)
     */
    protected function getCancelUrl(string $indexRoute, ?string $showRoute = null, ?Model $model = null): string
    {
        if ($this->isCreateMode) {
            return route($indexRoute);
        }

        if ($showRoute && $model) {
            return route($showRoute, $model);
        }

        return route($indexRoute);
    }
}
