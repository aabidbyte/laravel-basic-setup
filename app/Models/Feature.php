<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Feature\FeatureValueType;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Feature extends BaseModel
{
    use HasFactory;
    use HasTranslations;

    /**
     * @var array<int, string>
     */
    public array $translatable = ['name', 'description'];

    /**
     * @var string|null
     */
    protected $connection = 'central';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'name',
        'description',
        'type',
        'default_value',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => FeatureValueType::class,
            'default_value' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function tenantOverrides(): HasMany
    {
        return $this->hasMany(TenantFeatureOverride::class);
    }

    public function label(): string
    {
        return (string) $this->name;
    }
}
