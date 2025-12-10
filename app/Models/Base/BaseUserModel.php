<?php

namespace App\Models\Base;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Base user model class for all authenticatable models.
 *
 * This model includes the HasUuid trait to ensure all user models
 * automatically generate unique UUIDs when created.
 *
 * All new authenticatable models should extend this class instead of
 * Illuminate\Foundation\Auth\User directly.
 */
abstract class BaseUserModel extends Authenticatable
{
    use HasFactory, HasUuid, Notifiable;
}
