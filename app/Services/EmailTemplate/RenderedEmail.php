<?php

declare(strict_types=1);

namespace App\Services\EmailTemplate;

/**
 * Rendered Email DTO.
 *
 * Represents a fully rendered email ready for sending,
 * with subject, HTML content, plain-text fallback, and metadata.
 */
readonly class RenderedEmail
{
    public function __construct(
        public string $subject,
        public string $html,
        public string $text,
        public ?string $preheader = null,
        public ?string $locale = null,
        public ?string $templateName = null,
    ) {}

    /**
     * Get email content as an array (for Mailable use).
     *
     * @return array{subject: string, html: string, text: string, preheader: string|null}
     */
    public function toArray(): array
    {
        return [
            'subject' => $this->subject,
            'html' => $this->html,
            'text' => $this->text,
            'preheader' => $this->preheader,
        ];
    }
}
