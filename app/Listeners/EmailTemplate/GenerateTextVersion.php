<?php

declare(strict_types=1);

namespace App\Listeners\EmailTemplate;

use App\Events\EmailTemplate\EmailTemplateSaved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class GenerateTextVersion implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(EmailTemplateSaved $event): void
    {
        $template = $event->template;

        // Eager load translations if not loaded
        if (! $template->relationLoaded('translations')) {
            $template->load('translations');
        }

        foreach ($template->translations as $translation) {
            // Only convert if the locale matches the event locale (if provided) or for all if null
            if ($event->locale && $translation->locale !== $event->locale) {
                continue;
            }

            $hasChanges = false;

            // Convert draft HTML to draft Text
            if (! empty($translation->draft_html_content)) {
                $textContent = $this->convertHtmlToText($translation->draft_html_content);
                if ($translation->draft_text_content !== $textContent) {
                    $translation->draft_text_content = $textContent;
                    $hasChanges = true;
                }
            }

            // Also ensure published text is consistent if published HTML exists
            if (! empty($translation->html_content)) {
                $textContent = $this->convertHtmlToText($translation->html_content);
                if ($translation->text_content !== $textContent) {
                    $translation->text_content = $textContent;
                    $hasChanges = true;
                }
            }

            if ($hasChanges) {
                $translation->saveQuietly(); // Prevent infinite loops
            }
        }
    }

    protected function convertHtmlToText(string $html): string
    {
        return Str::of($html)
            // 1. Replace links with "text (url)" format
            ->replaceMatches('/<a[^>]+href="([^"]+)"[^>]*>(.*?)<\/a>/i', '$2 ($1)')
            // 2. Replace structural tags with newlines
            ->replaceMatches('/<br[^>]*>/i', "\n")
            ->replaceMatches('/<\/p>/i', "\n\n")
            ->replaceMatches('/<\/div>/i', "\n")
            ->replaceMatches('/<\/tr>/i', "\n")
            // 3. Strip all other tags
            ->stripTags()
            // 4. Decode HTML entities
            ->pipe(fn ($s) => html_entity_decode((string) $s))
            // 5. Clean up excessive whitespace
            ->replaceMatches('/^[ \t]+|[ \t]+$/m', '') // Trim lines
            ->replaceMatches('/\n{3,}/', "\n\n") // Max 2 newlines
            ->trim()
            ->toString();
    }
}
