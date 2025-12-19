<?php

namespace App\Models\Base;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Base user model class for all authenticatable models.
 *
 * This model includes the HasUuid trait to ensure all user models
 * automatically generate unique UUIDs when created.
 *
 * This model also includes the SoftDeletes trait to ensure all user models
 * support soft deletion by default.
 *
 * All new authenticatable models should extend this class instead of
 * Illuminate\Foundation\Auth\User directly.
 *
 * @see \App\Models\Base\BaseModel For regular models
 */
abstract class BaseUserModel extends Authenticatable
{
    use HasFactory, HasUuid, Notifiable, SoftDeletes;
}
