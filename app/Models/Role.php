<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'team_id',
        'uuid',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model): void {
            if (empty($model->getAttribute('uuid')) && empty($model->uuid)) {
                $model->setAttribute('uuid', (string) Str::uuid());
            }
        });

        static::saving(function ($model): void {
            if (empty($model->getAttribute('uuid')) && empty($model->uuid)) {
                $model->setAttribute('uuid', (string) Str::uuid());
            }
        });
    }
}
