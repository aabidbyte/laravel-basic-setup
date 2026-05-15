<?php

declare(strict_types=1);

namespace App\Services\Features;

use App\Enums\Feature\FeatureValueType;
use App\Models\Feature;

class FeatureValueNormalizer
{
    public function normalize(Feature|FeatureValueType|string|null $type, mixed $value): mixed
    {
        if ($value === null || (\is_string($value) && \trim($value) === '')) {
            return null;
        }

        $type = $this->type($type);

        return match ($type) {
            FeatureValueType::BOOLEAN => \in_array(\strtolower((string) $value), ['1', 'true', 'yes', 'on', 'enabled'], true),
            FeatureValueType::INTEGER => \is_numeric($value) ? (int) $value : $value,
            FeatureValueType::DECIMAL => \is_numeric($value) ? (float) $value : $value,
            FeatureValueType::STRING => \trim((string) $value),
        };
    }

    public function display(mixed $value): string
    {
        if (\is_bool($value)) {
            return $value ? __('common.yes') : __('common.no');
        }

        if ($value === null || $value === '') {
            return __('subscriptions.included');
        }

        return (string) $value;
    }

    private function type(Feature|FeatureValueType|string|null $type): FeatureValueType
    {
        if ($type instanceof Feature) {
            return $type->type ?? FeatureValueType::STRING;
        }

        if ($type instanceof FeatureValueType) {
            return $type;
        }

        return FeatureValueType::tryFrom((string) $type) ?? FeatureValueType::STRING;
    }
}
