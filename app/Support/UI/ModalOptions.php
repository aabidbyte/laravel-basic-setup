<?php

declare(strict_types=1);

namespace App\Support\UI;

/**
 * Data Object for opening a modal.
 */
readonly class ModalOptions
{
    /**
     * Create a new ModalOptions instance.
     *
     * @param  string|null  $viewPath  The view or component path
     * @param  string  $viewType  'blade' or 'livewire'
     * @param  array  $viewProps  Props to pass to the modal content
     * @param  string|null  $viewTitle  Optional modal title
     * @param  string|null  $datatableId  ID of the datatable for callbacks
     */
    public function __construct(
        public ?string $viewPath = null,
        public string $viewType = 'blade',
        public array $viewProps = [],
        public ?string $viewTitle = null,
        public ?string $datatableId = null,
    ) {}

    /**
     * Create a new ModalOptions instance from an array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            viewPath: $data['viewPath'] ?? null,
            viewType: $data['viewType'] ?? 'blade',
            viewProps: $data['viewProps'] ?? [],
            viewTitle: $data['viewTitle'] ?? null,
            datatableId: $data['datatableId'] ?? null,
        );
    }
}
