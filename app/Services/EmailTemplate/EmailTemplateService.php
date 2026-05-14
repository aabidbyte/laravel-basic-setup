<?php

declare(strict_types=1);

namespace App\Services\EmailTemplate;

use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Events\EmailTemplate\EmailTemplatePublished;
use App\Events\EmailTemplate\EmailTemplateSaved;
use App\Models\EmailTemplate\EmailTemplate;
use App\Models\EmailTemplate\EmailTranslation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmailTemplateService
{
    /**
     * Save drafts for an email template.
     *
     * @param  array<string, array<string, mixed>>  $translations
     */
    public function saveDraft(EmailTemplate $template, array $translations): void
    {
        DB::transaction(function () use ($template, $translations) {
            foreach ($translations as $locale => $data) {
                $draftData = [
                    'draft_subject' => $data['subject'] ?? null,
                    'draft_html_content' => $data['html_content'] ?? null,
                    'draft_preheader' => $data['preheader'] ?? null,
                ];

                $template->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $draftData,
                );
            }
        });

        EmailTemplateSaved::dispatch($template->fresh(['translations']));
    }

    /**
     * Publish an email template (promote drafts, set status, dispatch post-publish event).
     */
    public function publish(EmailTemplate $template): void
    {
        if ($template->is_system || $template->is_default) {
            return;
        }

        $template->loadMissing('translations');
        $this->assertMergeTagsValidForPublish($template);

        DB::transaction(function () use ($template) {
            $template->refresh();
            $template->load('translations');

            foreach ($template->translations as $translation) {
                $this->promoteTranslationDraftToPublished($translation);
            }

            $template->update(['status' => EmailTemplateStatus::PUBLISHED]);
        });

        EmailTemplatePublished::dispatch($template->fresh(['translations']));
    }

    /**
     * Restore published content to draft (discard draft changes).
     */
    public function restoreToDraft(EmailTemplate $template): void
    {
        DB::transaction(function () use ($template) {
            foreach ($template->translations as $translation) {
                $translation->update([
                    'draft_subject' => $translation->subject,
                    'draft_html_content' => $translation->html_content,
                    'draft_text_content' => $translation->text_content,
                    'draft_preheader' => $translation->preheader,
                ]);
            }
        });

        EmailTemplateSaved::dispatch($template->fresh(['translations']));
    }

    /**
     * @throws ValidationException
     */
    protected function assertMergeTagsValidForPublish(EmailTemplate $template): void
    {
        /** @var MergeTagEngine $engine */
        $engine = \app(MergeTagEngine::class);
        $entityTypes = $template->entity_types ?? [];
        $contextKeys = $template->context_variables ?? [];
        $messages = [];

        foreach ($template->translations as $translation) {
            $pairs = [
                'subject' => (string) ($translation->draft_subject ?? $translation->subject ?? ''),
                'html' => (string) ($translation->draft_html_content ?? $translation->html_content ?? ''),
                'text' => (string) ($translation->draft_text_content ?? $translation->text_content ?? ''),
            ];

            foreach ($pairs as $field => $content) {
                if ($content === '') {
                    continue;
                }

                $invalid = $engine->validateTags($content, $entityTypes, $contextKeys);

                foreach ($invalid as $tag) {
                    $messages[] = "{$translation->locale} ({$field}): invalid merge tag `{$tag}`";
                }
            }
        }

        if ($messages !== []) {
            throw ValidationException::withMessages([
                'publish' => $messages,
            ]);
        }
    }

    protected function promoteTranslationDraftToPublished(EmailTranslation $translation): void
    {
        if ($translation->draft_html_content === null) {
            return;
        }

        $translation->html_content = $translation->draft_html_content;
        $translation->text_content = $translation->draft_text_content;
        $translation->subject = $translation->draft_subject ?? $translation->subject;
        $translation->preheader = $translation->draft_preheader ?? $translation->preheader;

        $translation->draft_html_content = null;
        $translation->draft_text_content = null;
        $translation->draft_subject = null;
        $translation->draft_preheader = null;

        $translation->saveQuietly();
    }
}
