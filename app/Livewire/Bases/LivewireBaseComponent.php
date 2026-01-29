<?php

namespace App\Livewire\Bases;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Base component for all Livewire components.
 *
 * Provides centralized placeholder functionality with typed skeletons.
 * All Livewire components should extend this class.
 */
use App\Enums\Ui\PlaceholderType;

/**
 * Base component for all Livewire components.
 *
 * Provides centralized placeholder functionality with typed skeletons.
 * All Livewire components should extend this class.
 */
abstract class LivewireBaseComponent extends Component
{
    /**
     * Placeholder type for lazy loading skeleton.
     */
    protected PlaceholderType $placeholderType = PlaceholderType::DEFAULT;

    /**
     * Number of rows for the skeleton placeholder.
     */
    protected int $placeholderRows = 3;

    /**
     * Number of columns for table skeleton.
     */
    protected int $placeholderColumns = 4;

    /**
     * Additional classes for the placeholder container.
     */
    protected string $placeholderClass = '';

    /**
     * Render the placeholder view for lazy loading.
     */
    public function placeholder(): View
    {
        return view('components.ui.placeholder', [
            'type' => $this->placeholderType,
            'rows' => $this->placeholderRows,
            'columns' => $this->placeholderColumns,
            'class' => $this->placeholderClass,
        ]);
    }

    /**
     * Handle toJSON method call.
     * This seems to be triggered by some frontend serialization logic.
     */
    public function toJSON(): array
    {
        return [];
    }
}
