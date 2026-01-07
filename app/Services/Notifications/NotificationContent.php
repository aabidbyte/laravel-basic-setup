<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

class NotificationContent
{
    /**
     * Content type constants.
     */
    public const TYPE_STRING = 'string';

    public const TYPE_HTML = 'html';

    public const TYPE_VIEW = 'view';

    public function __construct(
        protected string|Htmlable|HtmlString|array|null $content = null,
        protected string $type = self::TYPE_STRING,
        protected ?string $viewPath = null,
        protected array $viewData = [],
    ) {}

    /**
     * Create content from a string or translatable array.
     *
     * @param  string|array{key: string, params: array}  $content
     */
    public static function string(string|array $content): static
    {
        return new static(
            content: $content,
            type: self::TYPE_STRING,
        );
    }

    /**
     * Create content from HTML (trusted).
     */
    public static function html(string|Htmlable|HtmlString $html): static
    {
        $htmlContent = $html instanceof HtmlString ? $html : new HtmlString($html);

        return new static(
            content: $htmlContent,
            type: self::TYPE_HTML,
        );
    }

    /**
     * Create content from a Blade view.
     * The view will be stored and rendered at display time for proper locale handling.
     */
    public static function view(string $view, array $data = []): static
    {
        return new static(
            content: null,
            type: self::TYPE_VIEW,
            viewPath: $view,
            viewData: $data,
        );
    }

    /**
     * Render the content to a string (immediate rendering).
     */
    public function render(): string
    {
        if ($this->type === self::TYPE_VIEW && $this->viewPath) {
            return View::make($this->viewPath, $this->viewData)->render();
        }

        if ($this->content === null) {
            return '';
        }

        // Check if content is a translatable array
        if (is_array($this->content) && isset($this->content['key'])) {
            return __($this->content['key'], $this->content['params'] ?? []);
        }

        if ($this->content instanceof Htmlable) {
            return $this->content->toHtml();
        }

        return (string) $this->content;
    }

    /**
     * Convert to storable array for database persistence.
     *
     * @return array{type: string, content?: string|array, view?: string, data?: array}
     */
    public function toStorable(): array
    {
        if ($this->type === self::TYPE_VIEW) {
            return [
                'type' => self::TYPE_VIEW,
                'view' => $this->viewPath,
                'data' => $this->viewData,
            ];
        }

        // Check if content is a translatable array
        if (is_array($this->content) && isset($this->content['key'])) {
            return [
                'type' => self::TYPE_STRING,
                'content' => $this->content, // Store the array directly
            ];
        }

        return [
            'type' => $this->type,
            'content' => $this->render(),
        ];
    }

    /**
     * Create from stored array and render immediately.
     *
     * @param  array{type: string, content?: string|array, view?: string, data?: array}  $data
     */
    public static function fromStorable(array $data): string
    {
        $type = $data['type'] ?? self::TYPE_STRING;

        if ($type === self::TYPE_VIEW && isset($data['view'])) {
            return View::make($data['view'], $data['data'] ?? [])->render();
        }

        $content = $data['content'] ?? '';

        // Check if content is a translatable array
        if (is_array($content) && isset($content['key'])) {
            return __($content['key'], $content['params'] ?? []);
        }

        return is_string($content) ? $content : '';
    }

    /**
     * Check if content exists.
     */
    public function hasContent(): bool
    {
        if ($this->type === self::TYPE_VIEW) {
            return $this->viewPath !== null;
        }

        return $this->content !== null && trim($this->render()) !== '';
    }

    /**
     * Get the raw content.
     */
    public function getContent(): string|Htmlable|HtmlString|null
    {
        return $this->content;
    }

    /**
     * Get the content type.
     */
    public function getType(): string
    {
        return $this->type;
    }
}
