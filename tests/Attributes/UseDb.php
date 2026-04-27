<?php

namespace Tests\Attributes;

use App\Enums\Database\ConnectionType;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class UseDb
{
    public function __construct(
        public readonly ConnectionType|string $connection = ConnectionType::TENANT,
    ) {}
}
