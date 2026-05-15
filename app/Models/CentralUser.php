<?php

declare(strict_types=1);

namespace App\Models;

class CentralUser extends User
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'central';

    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = 'users';
}
