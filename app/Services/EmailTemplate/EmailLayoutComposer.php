<?php

declare(strict_types=1);

namespace App\Services\EmailTemplate;

use App\Models\EmailTemplate\EmailTemplate;
use Illuminate\Support\Facades\Blade;

/**
 * Email Layout Composer Service.
 *
 * Composes email content with layouts using Blade rendering.
 * Handles merge tag escaping to prevent conflicts with Blade syntax.
 */
class EmailLayoutComposer
{
    /**
     * Compose content with a layout for HTML.
     *
     * @param  EmailTemplate  $layout  The layout template (is_layout=true)
     * @param  string  $content  The email body content
     * @param  string|null  $locale  The locale to use
     * @return string The composed HTML
     */
    public function compose(EmailTemplate $layout, string $content, ?string $locale = null): string
    {
        if (! $layout->isLayout()) {
            return $content;
        }

        $layoutContent = $this->getLayoutContent($layout, $locale, 'html');

        if (empty($layoutContent)) {
            return $content;
        }

        return $this->renderLayout($layoutContent, $content);
    }

    /**
     * Compose content with a layout for plain text.
     *
     * @param  EmailTemplate  $layout  The layout template (is_layout=true)
     * @param  string  $content  The email body content
     * @param  string|null  $locale  The locale to use
     * @return string The composed text
     */
    public function composeText(EmailTemplate $layout, string $content, ?string $locale = null): string
    {
        if (! $layout->isLayout()) {
            return $content;
        }

        $layoutContent = $this->getLayoutContent($layout, $locale, 'text');

        if (empty($layoutContent)) {
            return $content;
        }

        return $this->renderLayout($layoutContent, $content);
    }

    /**
     * Get layout content from translation.
     */
    protected function getLayoutContent(EmailTemplate $layout, ?string $locale, string $type): string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $layout->getTranslation($locale)
            ?? $layout->getTranslation(config('app.fallback_locale'));

        if ($translation === null) {
            return '';
        }

        return $type === 'html'
            ? ($translation->html_content ?? '')
            : ($translation->text_content ?? '');
    }

    protected function renderLayout(string $layoutContent, string $content): string
    {
        // Simple string replacement for {{ $slot }}
        // We use split/implode to handle the placeholder robustly without executing
        // the rest of the content as Blade code.
        $parts = \preg_split('/(\{\{\s*\$slot\s*\}\}|\{!!\s*\$slot\s*!!\})/', $layoutContent);

        return \implode($content, $parts);
    }
}
