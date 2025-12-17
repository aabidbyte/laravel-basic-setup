<?php

namespace App\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;

abstract class BasePageComponent extends Component
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
