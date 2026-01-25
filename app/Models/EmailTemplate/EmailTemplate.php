<?php

declare(strict_types=1);

namespace App\Models\EmailTemplate;

use App\Enums\EmailTemplate\EmailTemplateKind;
use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Enums\EmailTemplate\EmailTemplateType;
use App\Models\Base\BaseModel;
use App\Models\Pivots\EmailTemplateTeam;
use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Unified Email Template Model.
 *
 * Represents both email layouts (is_layout=true) and email contents (is_layout=false).
 * Layouts are reusable wrappers with header/footer styling.
 * Contents are the actual email messages that can reference a layout.
 *
 * @property EmailTemplateType $type
 * @property EmailTemplateStatus $status
 */
class EmailTemplate extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'email_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_layout',
        'layout_id',
        'type',
        'entity_types',
        'context_variables',
        'status',
        'is_system',
        'is_default',
        'all_teams',
        'preview',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_layout' => 'boolean',
            'type' => EmailTemplateType::class,
            'status' => EmailTemplateStatus::class,
            'entity_types' => 'array',
            'context_variables' => 'array',
            'is_system' => 'boolean',
            'is_default' => 'boolean',
            'all_teams' => 'boolean',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the layout for this content (self-referential).
     * Only applicable for contents (is_layout=false).
     */
    public function layout(): BelongsTo
    {
        return $this->belongsTo(self::class, 'layout_id');
    }

    /**
     * Get the contents using this layout.
     * Only applicable for layouts (is_layout=true).
     */
    public function contents(): HasMany
    {
        return $this->hasMany(self::class, 'layout_id');
    }

    /**
     * Get the translations for this template.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(EmailTranslation::class, 'translatable');
    }

    /**
     * The teams that belong to this template.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'email_template_team')
            ->using(EmailTemplateTeam::class)
            ->withTimestamps();
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope to layouts only.
     */
    public function scopeLayouts(Builder $query): Builder
    {
        return $query->where('is_layout', true);
    }

    /**
     * Scope to contents only (non-layouts).
     */
    public function scopeContents(Builder $query): Builder
    {
        return $query->where('is_layout', false);
    }

    /**
     * Scope to published templates only.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', EmailTemplateStatus::PUBLISHED);
    }

    /**
     * Scope to active (non-archived) templates.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', EmailTemplateStatus::ARCHIVED);
    }

    /**
     * Scope to templates available for a team.
     */
    public function scopeAvailableForTeam(Builder $query, ?int $teamId): Builder
    {
        return $query->where(function ($q) use ($teamId) {
            $q->where('all_teams', true);
            if ($teamId !== null) {
                $q->orWhereHas('teams', fn ($q) => $q->where('teams.id', $teamId));
            }
        });
    }

    // =========================================================================
    // ACCESSORS & HELPERS
    // =========================================================================

    /**
     * Get a human-readable label for this template.
     */
    public function label(): string
    {
        return $this->name;
    }

    public function isLayout(): bool
    {
        return $this->is_layout === true;
    }

    /**
     * Get the kind of validation.
     */
    public function kind(): EmailTemplateKind
    {
        return EmailTemplateKind::fromBoolean($this->isLayout());
    }

    /**
     * Check if this is a content (not a layout).
     */
    public function isContent(): bool
    {
        return $this->is_layout === false;
    }

    /**
     * Check if template is available for a given team.
     */
    public function isAvailableForTeam(?int $teamId): bool
    {
        if ($this->all_teams) {
            return true;
        }

        if ($teamId === null) {
            return false;
        }

        return $this->teams()->where('teams.id', $teamId)->exists();
    }

    // =========================================================================
    // TRANSLATION METHODS
    // =========================================================================

    /**
     * Get translation for a specific locale.
     */
    public function getTranslation(string $locale): ?EmailTranslation
    {
        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Get translation with fallback to default locale.
     */
    public function getTranslationWithFallback(string $locale, ?string $fallbackLocale = null): ?EmailTranslation
    {
        $fallbackLocale = $fallbackLocale ?? config('app.fallback_locale');
        $translation = $this->getTranslation($locale);

        if ($translation !== null) {
            return $translation;
        }

        return $this->getTranslation($fallbackLocale);
    }

    /**
     * Check if template has translation for a locale.
     */
    public function hasTranslation(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    /**
     * Get available locales for this template.
     *
     * @return array<string>
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    // =========================================================================
    // STATIC FINDERS
    // =========================================================================

    /**
     * Find a published content by name.
     */
    public static function findByName(string $name): ?self
    {
        return static::query()
            ->where('name', $name)
            ->published()
            ->first();
    }

    /**
     * Find a published layout by name.
     */
    public static function findLayoutByName(string $name): ?self
    {
        return static::query()
            ->layouts()
            ->where('name', $name)
            ->published()
            ->first();
    }

    /**
     * Get the default layout.
     */
    public static function getDefaultLayout(): ?self
    {
        return static::query()
            ->layouts()
            ->where('is_default', true)
            ->published()
            ->first();
    }
}
