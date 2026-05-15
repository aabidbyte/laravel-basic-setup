<?php

namespace App\Models;

class CentralUser extends User
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'central';
}
