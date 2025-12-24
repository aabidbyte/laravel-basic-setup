<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\Toast\ToastAnimation;
use App\Enums\Toast\ToastPosition;
use App\Enums\Toast\ToastType;

class ToastPayload
{
    public function __construct(
        public string $title,
        public ?string $subtitle = null,
        public ?string $content = null,
        public ToastType $type = ToastType::Success,
        public ToastPosition $position = ToastPosition::TopRight,
        public ToastAnimation $animation = ToastAnimation::Slide,
        public ?string $link = null,
        public ?string $iconHtml = null,
        public bool $enableSound = true,
    ) {}

    /**
     * Convert the payload to an array for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'content' => $this->content,
            'type' => $this->type->value,
            'position' => $this->position->value,
            'animation' => $this->animation->value,
            'link' => $this->link,
            'iconHtml' => $this->iconHtml,
            'enableSound' => $this->enableSound,
        ];
    }
}
