<?php

declare(strict_types=1);

namespace App\Enums\Feature;

enum FeatureValueType: string
{
    case BOOLEAN = 'boolean';
    case INTEGER = 'integer';
    case DECIMAL = 'decimal';
    case STRING = 'string';

    public function label(): string
    {
        return __("features.value_types.{$this->value}");
    }
}
