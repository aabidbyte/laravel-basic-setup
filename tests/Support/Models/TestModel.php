<?php

declare(strict_types=1);

namespace Tests\Support\Models;

use App\Models\Base\BaseModel;

class TestModel extends BaseModel
{
    protected $table = 'test_models';

    protected $fillable = ['name', 'uuid'];

    public function label(): string
    {
        return $this->name ?? 'Test Model';
    }
}
