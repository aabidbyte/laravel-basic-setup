<?php

declare(strict_types=1);

namespace App\Models\EmailTemplate;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Email Translation Model.
 *
 * Stores localized content for any email entity (Template or Layout).
 */
class EmailTranslation extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'translatable_id',
        'translatable_type',
        'locale',
        'subject',
        'html_content',
        'text_content',
        'preheader',
    ];

    /**
     * Get the parent translatable model (template or layout).
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get a human-readable label for this translation.
     */
    public function label(): string
    {
        return sprintf('%s (%s)', $this->translatable?->name ?? 'Unknown', $this->locale);
    }
}
