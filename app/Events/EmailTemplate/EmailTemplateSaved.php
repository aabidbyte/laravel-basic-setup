<?php

declare(strict_types=1);

namespace App\Events\EmailTemplate;

use App\Models\EmailTemplate\EmailTemplate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailTemplateSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public EmailTemplate $template,
        public ?string $locale = null
    ) {}
}
