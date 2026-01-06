<?php

namespace App\Livewire\Bases;

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

    public function boot(): void
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
}
