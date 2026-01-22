<?php

declare(strict_types=1);

namespace App\Models\Pivots;

use App\Models\EmailTemplate\EmailTemplate;
use App\Models\Team;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Email Template Team Pivot.
 *
 * Represents the many-to-many relationship between email templates and teams.
 */
class EmailTemplateTeam extends Pivot
{
    protected $table = 'email_template_team';

    public $incrementing = true;

    /**
     * Get the email template.
     */
    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    /**
     * Get the team.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
