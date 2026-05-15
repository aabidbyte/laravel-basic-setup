<?php

declare(strict_types=1);

namespace App\Events\EmailTemplate;

use App\Events\Base\BaseEvent;
use App\Models\EmailTemplate\EmailTemplate;
use Illuminate\Broadcasting\InteractsWithSockets;

class EmailTemplateSaved extends BaseEvent
{
    use InteractsWithSockets;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public EmailTemplate $template,
        public ?string $locale = null,
    ) {}
}
