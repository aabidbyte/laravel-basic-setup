<?php

declare(strict_types=1);

namespace App\Support\UI;

/**
 * Data Object for Icon rendering options.
 */
readonly class IconOptions
{
    /**
     * Create a new IconOptions instance.
     */
    public function __construct(
        public ?string $name = null,
        public ?string $pack = null,
        public ?string $class = null,
        public ?string $size = null,
        public ?string $color = null,
    ) {}

    /**
     * Create instance from array.
     *
     * @param  array{name?: string|null, pack?: string|null, class?: string|null, size?: string|null, color?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            pack: $data['pack'] ?? null,
            class: $data['class'] ?? null,
            size: $data['size'] ?? null,
            color: $data['color'] ?? null,
        );
    }
}
