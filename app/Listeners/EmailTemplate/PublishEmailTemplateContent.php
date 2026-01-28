<?php

declare(strict_types=1);

namespace App\Listeners\EmailTemplate;

use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Events\EmailTemplate\EmailTemplatePublished;
use Illuminate\Contracts\Queue\ShouldQueue;

class PublishEmailTemplateContent implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(EmailTemplatePublished $event): void
    {
        $template = $event->template;

        // Update template status
        $template->update(['status' => EmailTemplateStatus::PUBLISHED]);

        // Eager load translations
        if (! $template->relationLoaded('translations')) {
            $template->load('translations');
        }

        foreach ($template->translations as $translation) {
            $hasChanges = false;

            // Move draft content to published content ONLY if draft content exists
            if ($translation->draft_html_content !== null) {
                $translation->html_content = $translation->draft_html_content;
                $translation->text_content = $translation->draft_text_content;
                $translation->subject = $translation->draft_subject ?? $translation->subject;
                $translation->preheader = $translation->draft_preheader ?? $translation->preheader;

                // Clear drafts after publishing
                $translation->draft_html_content = null;
                $translation->draft_text_content = null;
                $translation->draft_subject = null;
                $translation->draft_preheader = null;

                $hasChanges = true;
            }

            if ($hasChanges) {
                $translation->saveQuietly();
            }
        }
    }
}
