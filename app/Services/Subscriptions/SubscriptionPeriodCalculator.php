<?php

declare(strict_types=1);

namespace App\Services\Subscriptions;

use App\Enums\Plan\PlanBillingCycle;
use App\Models\Plan;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class SubscriptionPeriodCalculator
{
    /**
     * @return array{starts_at: CarbonInterface, ends_at: CarbonInterface|null}
     */
    public function forPlan(Plan $plan, ?CarbonInterface $startsAt = null): array
    {
        $startsAt ??= Carbon::now();
        $cycle = $plan->billing_cycle instanceof PlanBillingCycle
            ? $plan->billing_cycle
            : PlanBillingCycle::tryFrom((string) $plan->billing_cycle);

        return [
            'starts_at' => $startsAt,
            'ends_at' => match ($cycle) {
                PlanBillingCycle::MONTHLY => $startsAt->copy()->addMonth(),
                PlanBillingCycle::YEARLY => $startsAt->copy()->addYear(),
                PlanBillingCycle::ONE_TIME, PlanBillingCycle::LIFETIME, null => null,
            },
        ];
    }
}
