<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

class NotificationContent
{
    public function __construct(
        protected string|Htmlable|HtmlString|null $content = null
    ) {}

    /**
     * Create content from a string.
     */
    public static function string(string $content): static
    {
        return new static($content);
    }

    /**
     * Create content from HTML (trusted).
     */
    public static function html(string|Htmlable|HtmlString $html): static
    {
        return new static($html instanceof HtmlString ? $html : new HtmlString($html));
    }

    /**
     * Create content from a Blade view.
     */
    public static function view(string $view, array $data = []): static
    {
        return new static(new HtmlString(View::make($view, $data)->render()));
    }

    /**
     * Render the content to a string.
     */
    public function render(): string
    {
        if ($this->content === null) {
            return '';
        }

        if ($this->content instanceof Htmlable) {
            return $this->content->toHtml();
        }

        return (string) $this->content;
    }

    /**
     * Check if content exists.
     */
    public function hasContent(): bool
    {
        return $this->content !== null && trim($this->render()) !== '';
    }

    /**
     * Get the raw content.
     */
    public function getContent(): string|Htmlable|HtmlString|null
    {
        return $this->content;
    }
}
